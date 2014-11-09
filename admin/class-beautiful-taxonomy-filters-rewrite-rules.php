<?php

/**
 * Generates all the rewrite rules for a given post type.
 *
 * @link       http://tigerton.se
 * @since      1.0.0
 *
 *
 * The rewrite rules allow a post type to be filtered by all possible combinations & permutations
 * of taxonomies that apply to the specified post type and additional query_vars specified with
 * the $query_vars parameter.
 *
 * Must be called from a function hooked to the 'generate_rewrite_rules' action so that the global
 * $wp_rewrite->preg_index function returns the correct value.
 *
 * @param string|object $post_type The post type for which you wish to create the rewrite rules
 * @param array $query_vars optional Non-taxonomy query vars you wish to create rewrite rules for. Rules will be created to capture any single string for the query_var, that is, a rule of the form '/query_var/(.+)/'
 *
 * @package    Beautiful_Taxonomy_Filters
 * @subpackage Beautiful_Taxonomy_Filters/admin
 * @author Brent Shepherd <me@brentshepherd.com>
 * @contributor Jonathan de Jong <jonathan@tigerton.se>
 * @since 1.0
 */

class Beautiful_Taxonomy_Filters_Rewrite_Rules {

	/**
	 * Helper to add new rewrite rule and a paginated version
	 *
	 * @since 1.0.1
	 * @global object $wp_rewrite core's WP_Rewrite instance
	 * @param array $rewrite_rules array of rewrite rules to add to
	 * @param string $new_rewrite_rule the new rewrite pattern
	 * @param string $new_query_string the corresponding query for the rewrite
	 * @param int $pagination_idx position in the pattern of pagination
	 * @return array ammended rewrite rules array
	 */
	function add_rule_with_pagination( $rewrite_rules, $new_rewrite_rule, $new_query_string, $pagination_idx ) {

		global $wp_rewrite;

		// Allow paging of filtered post type - WordPress expects 'page' in the URL but uses 'paged' in the query string so paging doesn't fit into our regex
		$new_paged_rewrite_rule = $new_rewrite_rule . 'page/([0-9]{1,})/';
		$new_paged_query_string = $new_query_string . '&paged=' . $wp_rewrite->preg_index( $pagination_idx );

		// Make the trailing backslash optional
		$new_paged_rewrite_rule = $new_paged_rewrite_rule . '?$';
		$new_rewrite_rule = $new_rewrite_rule . '?$';

		// Add the new rewrites
		$rewrite_rules += array(
			$new_paged_rewrite_rule => $new_paged_query_string,
			$new_rewrite_rule       => $new_query_string
		);

		return $rewrite_rules;

	}

	/**
	 * Generates all the rewrite rules for a given post type.
	 *
	 * The rewrite rules allow a post type to be filtered by all possible combinations & permutations
	 * of taxonomies that apply to the specified post type and additional query_vars specified with
	 * the $query_vars parameter.
	 * @param string|object $post_type The post type for which you wish to create the rewrite rules
	 * @param array $excluded_taxonomies The taxonomies that should be excluded from rewrite rules
	 * @since    1.0.0
	 */
	public function generate_rewrite_rules( $post_type, $excluded_taxonomies ) {

	    global $wp_rewrite;

	    if( ! is_object( $post_type ) )
	        $post_type = get_post_type_object( $post_type );

	    $new_rewrite_rules = array();
		$query_vars        = array();

	    $taxonomies = get_object_taxonomies( $post_type->name, 'objects' );

	    // Add taxonomy filters to the query vars array
	    foreach ( $taxonomies as $taxonomy ) {

			$query_var = $taxonomy->query_var;

			if ( ! is_array( $excluded_taxonomies ) || ! in_array( $query_var, $excluded_taxonomies ) ) {

				$query_vars[$query_var] = $taxonomy->rewrite ? $taxonomy->rewrite['slug'] : $query_var;

			}

	    }

		if ( true === $post_type->has_archive ) {

			$new_rewrite_rule_base = $post_type->rewrite['slug'] . '/';

		} else {

			$new_rewrite_rule_base = $post_type->has_archive . '/';

		}

		$new_query_string_base = 'index.php?post_type=' . $post_type->name;

		$query_var_combos    = $this->permute_all( array_keys( $query_vars ) );
		$rewrite_slug_combos = $this->permute_all( array_values( $query_vars ) );

		foreach ( $query_var_combos as $idx => $query_var_combo ) {

			$new_rewrite_rule = $new_rewrite_rule_base . implode( '/([^/]+)/', $rewrite_slug_combos[$idx] ) . '/([^/]+)/';

			$new_query_string = $new_query_string_base;

			foreach ( $query_var_combo as $i => $query_var ) {

				$new_query_string .= '&' . $query_var . '=' . $wp_rewrite->preg_index( $i + 1 );

			}

			$new_rewrite_rules = $this->add_rule_with_pagination( $new_rewrite_rules, $new_rewrite_rule, $new_query_string, $i + 2 );

		}

		return $new_rewrite_rules;

	}

	/**
	 * Build an array of all possible permutations of a given set
	 *
	 * i.e., [ a, b ] yields [ [ a ], [ a, b ], [ b ], [ b, a ] ]
	 *
	 * @since 1.0.1
	 * @param array $remaining
	 * @param array $next
	 * @param array $output
	 * @return array
	 */
	public function permute_all( $remaining, $next = array(), $output = array() ) {

		// build combinations of each item in $remaining and all items in $next
		foreach ( $remaining as $i => $in ) {

			// prepare the next value of $remaining by removing $in
			$next_remaining = $remaining;

			unset( $next_remaining[$i] );

			// create the combination for this call by adding the current item
			// from $remaining to the existing combination in $next
			$next_next = array_merge( $next, array( $in ) );

			// store the new combination
			$output[]  = $next_next;

			// find the remaining combinations
			$output    = $this->permute_all( $next_remaining, $next_next, $output );

		}

		// once $remaining is empty, return the combinations
		return $output;

	}

}
?>