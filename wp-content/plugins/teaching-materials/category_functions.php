<?php

/**
 * Returns html list of categories
 * @param $categories
 * @return string
 */
function getCatList($categories)
{
    $ret = '';
    $ret .= '<ul class="' . ($categories[0]->id == 1 ? '' : 'sortable') . '">';

    foreach ($categories as $category) {
        $classes = $category->type == 'node' ? 'strong cat-item' : 'cat-item';

        if ($category->id == 1) {
            $classes .= ' main-cat';
        }

        $ret .= '<li>';
        $ret .= '<span  class="' . $classes . '" data-toggle="collapse" data-target="#' . $category->id . '">' . $category->name . '</span>';
        if ($category->id != 1) {
            $ret .= '<a class="btn-cat btn-edit"  data-id="' . $category->id . '" data-toggle="tooltip" title="Muuda" href="javascript:void(0);"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>';
            $ret .= '<a href="?page=materjalid&task=delete&id=' . $category->id . '" class="delete-category btn-cat" data-cat-id="' . $category->id . '"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>';
        }
        $ret .= '</li>';
        if ($category->children) {
            $ret .= '<li>';
            $ret .= getCatList($category->children);
            $ret .= '</li>';
        }

    }
    $ret .= '</ul>';
    return $ret;
}

function getCategoryRows($categories, $depth = 0)
{
    $ret = '';

    foreach ($categories as $category) {
        $classes = $category->type == 'node' ? 'strong cat-item' : 'cat-item';

        if ($category->id != 1) {
            $ret .= '<tr>';
            $ret .= '<td>';
            $ret .= '<span class="' . $classes . '">' . str_repeat('<hr class="child-depth-marker"/>', $depth) . $category->name . '</span>';
            $ret .= '</td>';

            $ret .= '<td>';
            $ret .= '<a class="btn-cat btn-edit"  data-id="' . $category->id . '" data-toggle="tooltip" title="Muuda" href="javascript:void(0);"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>';
            $ret .= '<a type="button" href="#" class="delete-category btn-cat" data-id="' . $category->id . '"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>';
            $ret .= '</td>';
            $ret .= '</tr>';
        }

        if ($category->children) {
            $counter = $depth + 1;
            $ret .= getCategoryRows($category->children, $counter);
        }
    }
    return $ret;
}


/**
 * Returns one category by $id
 * @param $id
 * @return null|object
 */
function getCategoryById($id)
{
    global $wpdb;
    $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM category WHERE id = '%d'", [$id]));
    if ($result) {
        $result->siblingCount = getSiblingsCount($result->parent_id);
    }
    return $result;

}

function getMainCategory()
{
    $category = getCategoryById(1);
    $category->children = getChildren(1);
    $category->siblingCount = 0;
    return [$category];
}

/**
 * Returns tree structure of  all descending categories
 * @param $id
 * @return array|null
 */
function getChildren($id)
{
    global $wpdb;
    $result = $wpdb->get_results('SELECT category.name, category.id, category.content, category.type, category.order, category.parent_id FROM category WHERE category.parent_id = ' . $id . ' ORDER BY category.order');
    $count = count($result);
    if ($count > 0) {

        foreach ($result as $index => $category) {
            $result[$index]->siblingCount = $count - 1;
            $children = getChildren($category->id);

            if ($children) {
                $result[$index]->children = $children;
            }
        }
        return $result;
    } else {
        return null;
    }
}

function getChildCount($parentId)
{
    global $wpdb;
    return $wpdb->get_row('SELECT count(id) AS "count" FROM category WHERE category.parent_id = ' . $parentId)->count;
}

function getParentId($id)
{
    global $wpdb;
    return $wpdb->get_row('SELECT parent_id FROM category WHERE category.id = ' . $id)->parent_id;
}

/**
 * @return array|null
 */
function getUncategorized()
{
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM category WHERE parent_id IS NULL AND id != 1;");
}

/**
 * Set all child nodes null recursively
 * @param $id
 *
 */
function setChildrenNull($id)
{
    global $wpdb;
    $categories = $wpdb->get_results("SELECT * FROM category WHERE parent_id = $id;");

    if ($categories) {
        foreach ($categories as $category) {
            $wpdb->update('category', ['parent_id' => null, 'order' => 0], ['id' => $category->id]);
            setChildrenNull($category->id);
        }
    }
}

function saveCategory($data)
{
    global $wpdb;
    return $wpdb->insert('category', $data);
}


function updateCategory($data)
{
    global $wpdb;
    $wpdb->update('category', $data, ['id' => $data['id']]);
}


function deleteCategory($id)
{
    global $wpdb;
    if ($id == 1) {
        return null;
    }

    setChildrenNull($id);
    return $wpdb->delete('category', ['id' => $id]);
}


function getSiblingsCount($id)
{
    global $wpdb;
    return $wpdb->get_row('SELECT count(category.id) AS "count" FROM category WHERE category.parent_id = ' . $id)->count;

}

function getCategorySiblings($id)
{
    global $wpdb;
    $category = getCategoryById($id);
    if ($category) {
        return $wpdb->get_results('SELECT category.name, category.id, category.content, category.type, category.order, category.parent_id FROM category WHERE category.parent_id = ' . $category->parent_id . ' ORDER BY category.order');
    }
    return null;
}


function migrate($categories)
{
    global $wpdb;
    $order = 0;
    foreach ($categories as $category) {
        $wpdb->update('category', ['order' => $order++], ['id' => $category->id]);
        if ($category->children) {
            migrate($category->children);
        }
    }
}

function reorderSiblings($parentId)
{
    global $wpdb;
    $categories = getChildren($parentId);
    $order = 0;
    foreach ($categories as $category) {
        $wpdb->update('category', ['order' => $order++], ['id' => $category->id]);
    }
}