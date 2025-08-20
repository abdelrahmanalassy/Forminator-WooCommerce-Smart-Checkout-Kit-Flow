<?php
/**
 * Plugin Name: Forminator Role Control
 * Description: Change user role after submitting specific Forminator forms,
 *              using redirect pages after submission.
 */

add_action( 'template_redirect', function() {
    // Exit early if the user is not logged in
    if ( ! is_user_logged_in() ) return;
    
    // Get the currently logged-in user
    $user = wp_get_current_user();

    /**
     * Define a mapping between redirect page slugs and their corresponding user roles.
     * Each page slug (e.g. /role-switch-2/) is assigned to a role slug.
     * When the user lands on that page, their role will be updated.
     */
    $role_map = array(
        'role-switch-1' => 'submitted_team_data',
        'role-switch-2' => 'team_documents_uploaded',
        'role-switch-3' => 'technical_documents_uploaded',
        'role-switch-4' => 'onsite_pitching_ready',
    );

    // Loop through each page slug and check if the current page matches
    foreach ( $role_map as $page_slug => $target_role ) {
        if ( is_page( $page_slug ) ) {

            // Change the user's role to the target role
            // Use set_role() to replace all existing roles
            // If you want to keep existing roles, use add_role() instead
            $user->set_role( $target_role );
            
            // Log the role change (for debugging purposes)
            error_log("[RoleProgression] ✅ User {$user->ID} role changed to '{$target_role}' via page {$page_slug}");

            // Redirect the user after the role is updated
            wp_redirect(home_url('/my-account/'));
            exit;
        }
    }
});
