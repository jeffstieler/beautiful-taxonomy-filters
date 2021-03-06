<?php
/**
 * The publicly accessible functions of the plugin. These communicate with our objects inside the classes.
 *
 * @link       http://tigerton.se
 * @since      1.0.0
 *
 * @package    Beautiful_Taxonomy_Filters
 * @subpackage Beautiful_Taxonomy_Filters/includes
 * @author     Jonathan de Jong <jonathan@tigerton.se>
 */


/**
* Template tag for displaying the filters form
* @return html object
*/
function show_beautiful_filters(){

	return Beautiful_Taxonomy_Filters_Public::beautiful_filters();
}

/**
* Template tag for displaying the active filters info
* @return html object
*/
function show_beautiful_filters_info(){

	return Beautiful_Taxonomy_Filters_Public::beautiful_filters_info();
}

?>