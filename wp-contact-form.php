<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">

<?php

/*
Plugin Name: Contact-Form
Description: A wordpress contact-form plugin
Version: 1.0
Author: Saad TEBBA
Author github: https://github.com/SaadTebba
*/

// :::::::::::::::::::::::::::: When plugins activates create table ::::::::::::::::::::::::::::


function activation()
{

    global $wpdb;

    $table_name = $wpdb->prefix . 'wp_contact_form ';

    $query = "CREATE TABLE $table_name (
        `id` mediumint(9) NOT NULL AUTO_INCREMENT,
        `subject` varchar(255) NOT NULL,
        `fullname` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `message` text NOT NULL,
        `sending_time` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY (id) )";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($query);
}
register_activation_hook('wp-contact-form.php', 'activation');


// :::::::::::::::::::::::::::: When plugins desactivated delete table ::::::::::::::::::::::::::::


function deactivation()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_contact_form';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
register_deactivation_hook('wp-contact-form.php', 'deactivation');


// :::::::::::::::::::::::::::: Contact form - add short code ::::::::::::::::::::::::::::


function contact_form() 
{
    global $wpdb;

    $content = '';
    $content .= '<h3>Get in touch with us: </h3>';

    $content .= '<form method="POST" action="">';
    $content .= '<input type="text" class="form-control mb-2" placeholder="Your full name" name="fullname" required>';
    $content .= '<input type="text" class="form-control mb-2" placeholder="Subject" name="subject" required>';
    $content .= '<input type="email" class="form-control mb-2" placeholder="Your email" name="email" required>';
    $content .= '<textarea name="message" class="form-control" placeholder="Your message" cols="30" rows="10" required></textarea>';
    $content .= '<br><button type="submit" class="btn btn-primary" name="contact_submit">Submit</button>';
    $content .= '</form>';

    if (isset($_POST['contact_submit'])) {
        $table_name = $wpdb->prefix . 'wp_contact_form';
        $data = array(
            'subject' => $_POST['subject'],
            'fullname' => $_POST['fullname'],
            'email' => $_POST['email'],
            'message' => $_POST['message'],
            'sending_time' => current_time('mysql')
        );

        if ($wpdb->insert($table_name, $data)) {
            echo '<div class="alert alert-success d-flex align-items-center" role="alert"><p> Your message was sent succesfully. </p></div>';
        } else {
            echo '<div class="alert alert-success d-flex align-items-center" role="alert"><p> Something went wrong, try sending another message. </p></div>';
        }

    }

    return $content;
}
add_shortcode('contact_form', 'contact_form');


// :::::::::::::::::::::::::::: Plugin as side item in dashboard ::::::::::::::::::::::::::::


function contact_form_plugin_menu() {
    add_menu_page(
        'Contact Form',
        'Contact Form',
        'manage_options',
        'contact-form-plugin',
        'contact_form_plugin_page'
    );
}
add_action('admin_menu', 'contact_form_plugin_menu');

function contact_form_plugin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_contact_form';
    $messages = $wpdb->get_results("SELECT * FROM $table_name ORDER BY sending_time DESC");
  
    echo '<div class="wrap">';

    echo '<h1>Contact Form Messages</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';

    echo 
    '<thead>
        <tr>
            <th>Subject</th>
            <th>Name</th>
            <th>Email</th>
            <th>message</th>
            <th>Date Submitted</th>
        </tr>
    </thead>';

    echo '<tbody>';
    foreach ($messages as $message) {
      echo '<tr>';
        echo '<td>' . $message->subject . '</td>';
        echo '<td>' . $message->fullname . '</td>';
        echo '<td>' . $message->email . '</td>';
        echo '<td>' . $message->message . '</td>';
        echo '<td>' . $message->sending_time . '</td>';
      echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</div>';
  }