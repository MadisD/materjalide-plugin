<?php
get_stylesheet();
?>
<!--<script src="//cdn.ckeditor.com/4.6.2/full/ckeditor.js"></script>-->
<script src="//cdn.ckeditor.com/4.6.2/standard/ckeditor.js"></script>

<div class="main">
    <!--        <div class="col-lg-5">-->
    <!--            <h3>Loo uus kategooria</h3>-->
    <!--            --><?php //require_once('create.php') ?>
    <!--        </div>-->

    <div class="row">
        <div class="col-lg-2">
            <button id="create-cat-btn" class="btn btn-primary">Lisa uus kategooria</button>
        </div>
        <div style="text-align: right" class="col-lg-2 col-lg-offset-8">
            <a href="/materjalid" target="_blank" class="btn btn-primary">Vaata pealehel</a>
        </div>
    </div>

    <div id="categories">
        <?php require 'categories.php' ?>
    </div>
</div>

<!-- Modal -->
<div id="main-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
            </div>
        </div>

    </div>
</div>