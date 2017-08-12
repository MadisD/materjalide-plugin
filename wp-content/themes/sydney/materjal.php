<?php

/*

Template Name: Materjal

*/

global $wpdb;
$task = $_GET['task'];

switch ($task) {
    case 'root':
        header('Content-type: application/json');
        $result = getItems(1, 1, 1);

        $tipp = [
            'name' => 'Materjalid',
            'children' => $result,
            'className' => 'tipp',
            'height' => 0
        ];

        die(json_encode($tipp));
        break;
}
if ($task == 'content') {
    header('Content-type: application/json');
    $parentId = $_GET['parent_id'];
    $height = $_GET['height'];
    $tipp = getSingleCategory($parentId);

    if ($tipp) {
        $tipp->className = $tipp->type == 'node' ? 'tipp' : 'leht';
        $tipp->height = $height;
        $tipp->children = getItems($parentId, $height);
        $tipp->isRoot = true;
    }

    die(json_encode($tipp));
}


function getSingleCategory($id)
{
    global $wpdb;
    return $wpdb->get_row('SELECT * FROM category WHERE id = ' . $id);
}

function getItems($parentId, $height, $maxLevel = null)
{
    global $wpdb;

    $result = $wpdb->get_results('SELECT * FROM category WHERE category.parent_id = ' . $parentId . ' ORDER BY category.order');

    if (sizeof($result) > 0) {

        foreach ($result as $index => $category) {
            $className = $category->type == 'node' ? 'tipp' : 'leht';
            $result[$index]->className = $className;
            $result[$index]->height = $height;
            $result[$index]->parentId = $parentId;

            if (!$maxLevel || $height < $maxLevel) {
                $children = getItems($category->id, $height + 1, $maxLevel);

                if ($children) {
                    $result[$index]->children = $children;
                }
            }

        }
        return $result;
    } else {
        return null;
    }
}

get_header();

?>

<script src="/wp-content/themes/sydney/orgchart/js/jquery.orgchart.js"></script>

<div id="chart-container">
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<div id="chart_div"></div>

<script>
    jQuery(document).ready(function ($) {

        $(document).find('.page-wrap').removeClass();
        $(document).find('.content-wrapper').removeClass();
        $(document).find('#colophon').remove();

        function initContainer(url) {
            $('#chart-container').orgchart({
                'data': url,
                'nodeContent': 'content',
                'pan': true,
                'zoom': false,
                'toggleSiblingsResp': false,
                'createNode': function (node, data) {
                    $(node).find('i').remove();
                    if (!data.content) {
                        $(node).find('.content').remove();
                    }

                    $(node).data('parent-id', data.parentId);

                    if (data.height === 1) {

                        var drillDownIcon = $('<i>', {
                            'class': 'fa fa-arrow-circle-down drill-icon',
                            'click': function () {
                                $('#chart-container').html('');
                                initContainer('?task=content&parent_id=' + data.id + '&height=' + data.height);
                            }
                        });

                        $(node).append(drillDownIcon);

                    }

                    if (data.isRoot) {
                        var drillUpIcon = $('<i>', {
                            'class': 'fa fa-arrow-circle-up drill-icon',
                            'click': function () {
                                $('#chart-container').html('');
                                initContainer('?task=root');
                            }
                        });
                        $(node).prepend(drillUpIcon);
                    }
                }
            });
        }

        initContainer('?task=root');

    });
</script>

<?php get_footer(); ?>
