<?php
/*
Plugin Name: Gestionale Fornitori
Description: A plugin to manage suppliers with a dashboard and backend/frontend links.
Version: 1.0
Author: <a href="https://www.davecamerini.it">Davecamerini</a>
*/

// Hook to add the admin menu
add_action('admin_menu', 'gf_add_admin_menu');

function gf_add_admin_menu() {
    // Add top-level menu
    add_menu_page(
        'Gestionale Fornitori', // Page title
        'Gestionale Fornitori', // Menu title
        'manage_options', // Capability
        'gestionale-fornitori', // Menu slug
        '', // Function (not needed for top-level menu)
        'dashicons-admin-generic', // Icon
        6 // Position
    );

    // Add submenus
    add_submenu_page(
        'gestionale-fornitori', // Parent slug
        'Dashboard fornitore', // Page title
        'Dashboard fornitore', // Menu title
        'manage_options', // Capability
        'gestionale-fornitori', // Menu slug
        'gf_dashboard_link' // Function to handle the link
    );

    add_submenu_page(
        'gestionale-fornitori', // Parent slug
        'Fornitori', // Page title
        'Fornitori', // Menu title
        'manage_options', // Capability
        'gestionale-backend', // Menu slug
        'gf_backend_link' // Function to handle the link
    );

    add_submenu_page(
        'gestionale-fornitori', // Parent slug
        'Borghi', // Page title
        'Borghi', // Menu title
        'manage_options', // Capability
        'gestionale-frontend', // Menu slug
        'gf_frontend_link' // Function to handle the link
    );
}

// Function to redirect to the Dashboard URL
function gf_dashboard_link() {
    wp_redirect('https://www.lovenozze.it/fornitori/admin/dashboard.php'); // Replace with your actual URL
    exit;
}

// Function to redirect to the Backend URL
function gf_backend_link() {
    wp_redirect('https://www.lovenozze.it/fornitori/'); // Replace with your actual URL
    exit;
}

// Function to redirect to the Frontend URL
function gf_frontend_link() {
    wp_redirect('https://www.lovenozze.it/borghi/'); // Replace with your actual URL
    exit;
}
