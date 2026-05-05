<form action="../../Controllers/CouponController.php" method="POST">
    <label>Coupon Code:</label>
    <input type="text" name="code" placeholder="e.g. SUMMER20" required>
    
    <label>Discount %:</label>
    <input type="number" name="discount_percentage" min="1" max="100" required>
    
    <label>Apply to Category:</label>
    <select name="category_id">
        <option value="1">Electricity</option> 
        <option value="2">Power Tools</option>
    </select>
    
    <label>Expiry Date:</label>
    <input type="date" name="expiry_date" required>
    
    
    <button type="submit" name="add_campaign">Create Campaign</button>
</form>