<?php
add_action('wp_ajax_get_create_content', 'getCreateContent');
add_action('wp_ajax_get_edit_content', 'getEditContent');
add_action('wp_ajax_create_category', 'ajaxCreateCategory');
add_action('wp_ajax_delete_category', 'ajaxDeleteCategory');
add_action('wp_ajax_update_category', 'ajaxUpdateCategory');
add_action('wp_ajax_get_sibling_count', 'ajaxGetSiblingCount');
add_action('wp_ajax_get_categories_content', 'getCategoriesHtml');

function getCreateContent()
{
    header('Content-Type: application/json');
    $categories = getMainCategory();
    $html = loadTemplate('views/create.php', [
        'categories' => $categories
    ]);

    die(json_encode(['status' => 1, 'html' => $html]));
}

function getEditContent()
{
    header('Content-Type: application/json');
    $id = $_POST['id'];
    $category = getCategoryById($id);
    $categories = getMainCategory();

    $html = loadTemplate('views/muuda.php', [
        'category' => $category,
        'categories' => $categories
    ]);

    die(json_encode(['status' => 1, 'html' => $html]));
}

function ajaxCreateCategory()
{
    header('Content-Type: application/json');
    $data = [];
    $status = 0;
    $html = null;
    parse_str($_POST['data'], $data);
    $data['content'] = stripslashes($data['content']);

    if ($data['parent_id'] == '-1') {
        unset($data['parent_id']);
    } else {
        $data['order'] = getChildCount($data['parent_id']);
    }

    if (saveCategory($data)) {
        $status = 1;
        $html = getCategoriesHtml();
    };

    die(json_encode([
        'status' => $status,
        'html' => $html,
    ]));
}

function ajaxDeleteCategory()
{
    header('Content-Type: application/json');
    $id = $_POST['id'];
    $status = 0;
    $html = null;
    $category = getCategoryById($id);

    if (deleteCategory($id)) {
        reorderSiblings($category->parent_id);
        $status = 1;
        $html = getCategoriesHtml();
    };

    die(json_encode([
        'status' => $status,
        'html' => $html
    ]));
}


function ajaxUpdateCategory()
{
    header('Content-Type: application/json');
    $data = [];
    parse_str($_POST['data'], $data);
    $data['content'] = stripslashes($data['content']);
    $id = $data['id'];
    $category = getCategoryById($id);
    $parentId = $category->parent_id;
    $newOrder = $data['order'];

    if ($data['parent_id'] == '-1') {
        $data['parent_id'] = null;
    }

    if ($parentId == null) {
        updateCategory($data);
    } elseif ($parentId == $data['parent_id']) {
        if ($newOrder == $category->order) {
            updateCategory($data);
        } else {
            global $wpdb;
            $siblings = getCategorySiblings($id);
            moveElement($siblings, $category->order, $newOrder);
            foreach ($siblings as $key => $sibling) {
                if ($sibling->id == $id) {
                    updateCategory($data);
                } else {
                    $wpdb->update('category', ['order' => $key], ['id' => $sibling->id]);
                }
            }
        }
    } elseif ($parentId != $data['parent_id']) {
        global $wpdb;
        $tempOrder = getSiblingsCount($data['parent_id']);
        $data['order'] = $tempOrder;
        updateCategory($data);
        reorderSiblings($parentId);
        if ($newOrder != $tempOrder) {
            $siblings = getCategorySiblings($id);
            moveElement($siblings, $tempOrder, $newOrder);
            foreach ($siblings as $key => $sibling) {
                $wpdb->update('category', ['order' => $key], ['id' => $sibling->id]);
            }
        }
    }

    die(json_encode([
        'status' => 1,
        'html' => getCategoriesHtml(),
    ]));
}

function moveElement(&$array, $a, $b)
{
    $out = array_splice($array, $a, 1);
    array_splice($array, $b, 0, $out);
}

function getCategoriesHtml()
{
    return loadTemplate('views/categories.php', [
        'categories' => getMainCategory(),
        'uncategorized' => getUncategorized()
    ]);
}

function changeOrder()
{
    header('Content-Type: application/json');
    $order = $_POST['order'];
    $direction = $_POST['direction'];
    $id = $_POST['id'];

    $siblings = getCategorySiblings($id);
    $category = $siblings[$order];
    $category->order = $order + $direction;
    $nextCategory = $siblings[$order + $direction];
    $nextCategory->order = $nextCategory->order - $direction;

    global $wpdb;
    $wpdb->update('category', ['order' => $category->order], ['id' => $category->id]);
    $wpdb->update('category', ['order' => $nextCategory->order], ['id' => $nextCategory->id]);

    $categories = [getCategoryById(1)];
    $categories[0]->children = getChildren(1);
    $categories[0]->siblingCount = 0;
    $html = getCatList($categories);
    die(json_encode(['status' => 1, 'html' => $html]));
}

function ajaxGetSiblingCount()
{
    header('Content-Type: application/json');
    $parent_id = $_POST['parent_id'];
    $count = getSiblingsCount($parent_id) + 1;
    $html = '';
    if ($parent_id == '-1') {
        $html = '<option selected="selected" value="0">0</option>';
    } else {
        for ($i = 0; $i < $count; $i++) {
            $html .= '<option ' . ($i + 1 == $count ? 'selected="selected"' : '') . ' value="' . $i . '" > ' . ($i + 1) . '</option>';
        }
    }

    die(json_encode([
        'count' => $count,
        'html' => $html
    ]));
}