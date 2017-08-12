<table class="table table-bordered table-hover cat-table">
    <tr>
        <th style="font-size: 18px">Materjalid</th>
        <th style="width: 90px;"></th>
    </tr>
    <?= getCategoryRows($categories[0]->children) ?>
</table>

<table class="table table-bordered table-hover cat-table">
    <tr>
        <th style="font-size: 18px">Ilma kategooriata</th>
        <th style="width: 90px;"></th>
    </tr>
    <?= getCategoryRows($uncategorized) ?>
</table>

