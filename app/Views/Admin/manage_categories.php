<h2>Tool Taxonomy & Category Mapping</h2>

<form action="index.php?action=add_category" method="POST" style="margin-bottom: 20px; background: #f9f9f9; padding: 15px; border-radius: 5px;">
    <input type="text" name="name" placeholder="Category Name" required style="padding: 8px;">
    <input type="text" name="description" placeholder="Description" style="padding: 8px;">
    <button type="submit" style="padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px;">Add Category</button>
</form>

<table border="1" width="100%" style="border-collapse: collapse; text-align: left;">
    <thead style="background: #eee;">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty($categories)): foreach($categories as $cat): ?>
            <tr>
                <td><?= $cat['id'] ?></td>
                <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                <td><?= htmlspecialchars($cat['description']) ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="3" align="center">No categories found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>