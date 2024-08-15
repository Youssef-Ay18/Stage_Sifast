<?php
// If the form is submitted, add the person to the database
if (isset($_POST['submit'])) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'myplugin_data';
    
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $age = intval($_POST['age']);
    
    myplugin_add_person($first_name, $last_name, $age);
}
?>

<div class="myplugin-form">
    <form method="post">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" required>
        
        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" required>
        
        <label for="age">Age:</label>
        <input type="number" id="age" name="age" required>
        
        <input type="submit" name="submit" value="Submit">
    </form>
</div>
