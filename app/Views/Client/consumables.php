<?php

$consumables = $data['consumables'] ?? [
    [
        'id' => 1,
        'name' => 'Router Bit Set (12pc)',
        'tool' => 'Plunge Router',
        'stock' => 15,
        'unit' => 'Sets',
        'min_limit' => 5
    ],
    [
        'id' => 2,
        'name' => 'Sanding Discs (P80)',
        'tool' => 'Orbital Sander',
        'stock' => 4,
        'unit' => 'Pcs',
        'min_limit' => 10
    ],
    [
        'id' => 3,
        'name' => '3D Printing Filament (Black)',
        'tool' => 'Creality Ender 3',
        'stock' => 0,
        'unit' => 'Spools',
        'min_limit' => 2
    ]
];
?>


<link rel="stylesheet" href="../../assets/css/consumables.css">

<div class="container">
    <div class="header-flex">
        <h2 class="brand-text">CONSUMABLES INVENTORY</h2>
       
        <div class="role-badge role-admin">LENDER ACCESS</div>
    </div>

    <div class="table-wrapper">
        <table class="dark-table">
            <thead>
                <tr>
                    <th>CONSUMABLE ITEM</th>
                    <th>LINKED TOOL</th>
                    <th>STOCK</th>
                    <th>STATUS</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($consumables)): ?>
                    <?php foreach ($consumables as $item): 
                        
                        $is_low = ($item['stock'] <= $item['min_limit']);
                        $is_empty = ($item['stock'] <= 0);
                        
                        $pill_class = 'pill-green';
                        $status_text = 'Stable';

                        if ($is_empty) {
                            $pill_class = 'pill-red';
                            $status_text = 'Out of Stock';
                        } elseif ($is_low) {
                            $pill_class = 'pill-red';
                            $status_text = 'Low Stock';
                        }
                    ?>
                        <tr>
                            <td class="primary"><?= htmlspecialchars($item['name']); ?></td>
                            <td><?= htmlspecialchars($item['tool']); ?></td>
                            <td><?= $item['stock'] . ' ' . $item['unit']; ?></td>
                            <td>
                                <span class="pill <?= $pill_class; ?>">
                                    <?= $status_text; ?>
                                </span>
                            </td>
                            <td>
                              
                                <form action="../../Controllers/InventoryController.php?action=restock" method="POST" style="display:inline;">
                                    <input type="hidden" name="item_id" value="<?= $item['id']; ?>">
                                    <button type="submit" class="btn-sm">
                                        <i class="fas fa-plus-circle"></i> Restock
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-dim);">
                            No consumable items found in the database.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
