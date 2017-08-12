<?php

function getOptions($categories, $level = 0, $value = '', $category = null)
{
    $ret = '';
    foreach ($categories as $cat) {
        if (!$category || $category->id != $cat->id) {
            $ret .= '<option value="' . $cat->id . '"' . ($value == $cat->id ? 'selected="selected"' : '') . '>' . (str_repeat('&nbsp;', $level * 2)) . $cat->name . '</option>';
        }

        if ($cat->children) {
            $nextLevel = $level + 1;
            $ret .= getOptions($cat->children, $nextLevel, $value, $category);
        }
    }
    return $ret;
}

?>

<form action="<?= $form_action ?>" id="<?= $form_id ?>" method="post">
    <?php echo $category->id ? '<input type="hidden" class="hidden" name="id" value="' . $category->id . '">' : '' ?>
    <div class="form-group">
        <?php $key = 'name' ?>
        <?php $value = $category->$key ?>
        <div class="row">
            <div class="col-lg-3">
                <label for="<?= $key ?>">Nimetus</label>
            </div>
            <div class="col-lg-9">
                <input id="<?= $key ?>" type="text" class="form-control col-lg-8" value="<?= $value ?>"
                       name="<?= $key ?>">
            </div>

        </div>

    </div>

    <div class="form-group">
        <?php $key = 'parent_id' ?>
        <?php $value = $category->$key ?>
        <div class="row">
            <div class="col-lg-3">
                <label for="<?= $key ?>">Vanemkategooria</label>
            </div>
            <div class="col-lg-9">
                <select class="form-control" name="<?= $key ?>" id="parent-select">
                    <option value="-1">-Puudub-</option>
                    <?= getOptions($categories, 0, $value, $category) ?>
                </select>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?php $key = 'type' ?>
        <?php $value = $category->$key ?>
        <div class="row">
            <div class="col-lg-3">
                <label for="<?= $key ?>">Tüüp</label>
            </div>
            <div class="col-lg-9">
                <select class="form-control" name="<?= $key ?>">
                    <option <?php echo $value == 'leaf' ? 'selected="selected"' : ''; ?> value="leaf">Materjal</option>
                    <option <?php echo $value == 'node' ? 'selected="selected"' : ''; ?> value="node">Üldkategooria
                    </option>
                </select>
            </div>
        </div>
    </div>

    <?php if ($form_id === 'edit-form') { ?>
        <div class="form-group">
            <?php $key = 'order' ?>
            <?php $value = $category->$key ?>
            <div class="row">
                <div class="col-lg-3">
                    <label for="<?= $key ?>">Järjekord</label>
                </div>
                <div class="col-lg-9">
                    <select class="form-control" name="<?= $key ?>" id="order-select">
                        <?php
                        if (!$category->parent_id) { ?>
                            <option selected="selected" value="0">0</option>
                            <?php
                        } else {
                            for ($i = 0; $i < $category->siblingCount; $i++) { ?>
                                <option
                                    <?= $category->order == $i ? 'selected="selected"' : '' ?>value="<?= $i ?>"><?= ($i + 1) ?></option>
                            <?php }
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
    <?php } ?>


    <div class="form-group">
        <?php $key = 'content' ?>
        <?php $value = $category->$key ?>
        <label for="<?= $key ?>">Sisu</label>
        <textarea name="<?= $key ?>" id="<?= $form_id . '-' . $key ?>" rows="10" cols="10"><?= $value ?></textarea>
    </div>

    <input type="submit" value="<?= $form_submit_text ?>" class="btn btn-primary btn-md">
    <button type="button" class="btn btn-danger" data-dismiss="modal">Loobu</button>
</form>

<script>
    var type = '<?= $form_id; ?>';
    CKEDITOR.replace(type + '-content');
</script>