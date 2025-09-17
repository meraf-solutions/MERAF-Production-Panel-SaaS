<?php foreach ($rows as $rowNum => $row): $rowNum++; ?>
    <tr data-toggle="modal" data-target="#rowModal-<?= $rowNum ?>">
        <?php foreach ($row as $key => $value): ?>
            <td class="p-3 entry-cell">
                <?= htmlspecialchars(strlen($value) > 70 ? substr($value, 0, 70) . '...' : $value) ?>
            </td>
        <?php endforeach; ?>
    </tr>
<?php endforeach; ?>
