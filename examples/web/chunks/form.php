<form action="<?= htmlentities($_SERVER['PHP_SELF']); ?>" method="GET">
    <input type="text" name="query" value="<?= (isset($query)) ? $query : '' ?>" required>
    <input type="submit" value="Translate" >
    <p>Source language: <select name="source" required>
        <?php
            $dests = $langs;
            array_shift($dests);
            makeSelect($dests, $selected_source);
        ?>
    </select></p>
    <p>Destination language: <select name="dest" required>
        <?php makeSelect($langs, $selected_dest); ?>
    </select></p>
</form>
<?php

function makeSelect($langs, $preselected)
{
    foreach($langs as $key => $lang) {
        $selected = ($key === $preselected) ? 'selected' : '';
        echo '<option ' . $selected . ' value="' . $key . '">' . $lang . '</option>';
    }
}
