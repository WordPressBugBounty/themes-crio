<?php
/**
 * Customizer Panel Functionality
 *
 * @link http://www.boldgrid.com
 *
 * @since 2.0.0
 *
 * @package Boldgrid_Theme_Framework_Customizer
 */
if ( class_exists( 'WP_Customize_Panel' ) ) {

	/**
	 * Class: Boldgrid_Framework_Customizer_Panel
	 *
	 * Extends the WordPress customizer's panel implementation..
	 *
	 * @since      2.0.0
	 * @category   Customizer
	 * @package    Boldgrid_Framework
	 * @subpackage Boldgrid_Framework_Customizer
	 * @author     BoldGrid <support@boldgrid.com>
	 * @link       https://boldgrid.com
	 */
	class Boldgrid_Framework_Customizer_Panel extends WP_Customize_Panel {

		/**
		 * Panel in which to show the panel, making it a sub-panel.
		 *
		 * @since 2.0.0
		 *
		 * @var string
		 *
		 * @access public
		 */
		public $panel;

		/**
		 * Panel type
		 *
		 * @access public
		 * @var    string The type of panel to create.
		 */
		public $type = 'bgtfw_panel';

		/**
		 * Panel breadcrumb
		 *
		 * @since 2.0.0
		 *
		 * @access public
		 * @var    string The full breadcrumb.
		 */
		public $breadcrumb = '';

		/**
		 * Panel notification defaults.
		 *
		 * @since 2.1.1
		 *
		 * @access public
		 * @var    Array BGTFW Notice defaults.
		 */
		public $notice_defaults = [
			'dismissible' => false,
			'message' => '',
			'type' => 'bgtfw-features',
			'templateId' => 'bgtfw-notification',
			'features' => [],
		];

		/**
		 * Panel notifications.
		 *
		 * @since 2.1.1
		 *
		 * @access public
		 * @var    Array BGTFW Notice defaults.
		 */
		public $notice = [];

		/**
		 * Panel Icon
		 *
		 * @since 2.1.1
		 *
		 * @access public
		 * @var    String $icon Panel icon.
		 */
		public $icon = null;

		/**
		 * Gather the parameters passed to client JavaScript via JSON.
		 *
		 * @since  2.0.0
		 *
		 * @return array The array to be exported to the client as JSON.
		 */
		public function json() {
			$array = wp_array_slice_assoc( (array) $this, array( 'id', 'description', 'priority', 'type', 'panel' ) );

			$array['title'] = html_entity_decode( $this->title, ENT_QUOTES, get_bloginfo( 'charset' ) );
			$array['content'] = $this->get_content();
			$array['active'] = $this->active();
			$array['instanceNumber'] = $this->instance_number;
			$array['breadcrumb'] = $this->get_breadcrumb();
			$array['icon'] = $this->get_icon();

			$_notice = $this->notice;

			if ( isset( $_notice ) && ! empty( $_notice ) ) {
				$this->notice = wp_parse_args( $_notice, $this->notice_defaults );
				$array['notice'] = $this->notice;
			}

			return $array;
		}

		/**
		 * Get the breadcrumb trails for the current panel.
		 *
		 * @since 2.0.0
		 *
		 * @return string $breadcrumb The breadcrumb trail displayed.
		 */
		public function get_icon() {
			if ( ! empty( $this->icon ) ) {
				if ( strpos( $this->icon, 'dashicons-' ) !== false ) {
					$this->icon = "dashicons-before {$this->icon}";
				} else if ( strpos( $this->icon, 'fa-' ) !== false ) {
					$this->icon = "fa {$this->icon}";
				} else {
					$this->icon = $this->icon;
				}
			}

			return $this->icon;
		}

		/**
		 * Get the breadcrumb trails for the current panel.
		 *
		 * @since 2.0.0
		 *
		 * @return string $breadcrumb The breadcrumb trail displayed.
		 */
		public function get_breadcrumb() {
			$panel = $this->panel;
			$this->breadcrumb = '<span class="dashicons dashicons-admin-home"></span>';

			if ( ! empty( $panel ) ) {
				$breadcrumb = $this->manager->get_panel( $panel )->breadcrumb;
				if ( ! empty( $breadcrumb ) ) {
					$this->breadcrumb = rtrim( $breadcrumb ) . ' &#9656; ' . $this->get_panel_link( $this->manager->get_panel( $panel )->id, $this->manager->get_panel( $panel )->title );
				}
			}

			return $this->breadcrumb;
		}

		/**
		 * Generates the html link for the panel's breadcrumb.
		 *
		 * @since 2.0.0
		 *
		 * @param string $id         The panel's ID.
		 * @param string $title      The panel's title.
		 * @param bool   $section_id Section should supply it's ID if calling panel.
		 *
		 * @return string The panel's html link.
		 */
		public function get_panel_link( $id, $title, $section_id = '' ) {
			if ( ! empty( $section_id ) ) {
				$section_id = ' wp.customize.section( \'' . esc_js( $section_id ) . '\' ).collapse();';
			}

			return '<a href="#" title="' . esc_attr( $title ) . '" onclick="event.preventDefault();' . $section_id . ' wp.customize.panel( \'' . esc_js( $id ) . '\' ).expand();">' . esc_html( $title ) . '</a>';
		}

		/**
		 * An Underscore (JS) template for this panel's content (but not its container).
		 *
		 * Class variables for this panel class are available in the `data` JS object;
		 * export custom variables by overriding WP_Customize_Panel::json().
		 *
		 * @see WP_Customize_Panel::print_template()
		 *
		 * @since 4.3.0
		 */
		protected function content_template() {
			?>
			<li class="panel-meta customize-info accordion-section <# if ( ! data.description ) { #> cannot-expand<# } #>">
				<button class="customize-panel-back" tabindex="-1"><span class="screen-reader-text"><?php esc_html_e( 'Back', 'crio' ); ?></span></button>
				<div class="accordion-section-title">
					<span class="preview-notice">
						{{{ data.breadcrumb }}}
						<strong class="panel-title<# if ( ! _.isEmpty( data.icon ) ) { #> {{ data.icon }}<# } #>">{{ data.title }}</strong>
					</span>
					<# if ( data.description ) { #>
						<button type="button" class="customize-help-toggle dashicons dashicons-editor-help" aria-expanded="false"><span class="screen-reader-text"><?php esc_html_e( 'Help', 'crio' ); ?></span></button>
					<# } #>
				</div>
				<# if ( data.description ) { #>
					<div class="description customize-panel-description">
						{{{ data.description }}}
					</div>
				<# } #>

				<div class="customize-control-notifications-container"></div>
			</li>
			<?php
		}

		/**
		 * An Underscore (JS) template for rendering this panel's container.
		 *
		 * Class variables for this panel class are available in the `data` JS object;
		 * export custom variables by overriding WP_Customize_Panel::json().
		 *
		 * @see WP_Customize_Panel::print_template()
		 *
		 * @since 4.3.0
		 */
		protected function render_template() {

			// Strip the beta or RC suffix from the version number.
			$wp_version = preg_replace( '/-.*/', '', get_bloginfo( 'version' ) );
			// If wordpress version is less than 6.7 then use the old template
			if ( version_compare( $wp_version, '6.7', '<' ) ) {
				$this->old_render_template();
				return;
			}
			?>
			<li id="accordion-panel-{{ data.id }}" class="accordion-section control-section control-panel control-panel-{{ data.type }}">
			<h3 class="wp67 accordion-section-title<# if ( ! _.isEmpty( data.icon ) ) { #> {{ data.icon }}<# } #>" tabindex="0">
					<button type="button" class="accordion-trigger" aria-expanded="false" aria-controls="{{ data.id }}-content">
						{{ data.title }}
					</button>
				</h3>
				<ul class="accordion-sub-container control-panel-content" id="{{ data.id }}-content"></ul>
			</li>
		<?php
		}

		/**
		 * An Underscore (JS) template for rendering this panel's container in
		 * versions of WP older than 6.7.
		 *
		 * Class variables for this panel class are available in the `data` JS object;
		 * export custom variables by overriding WP_Customize_Panel::json().
		 *
		 * @see WP_Customize_Panel::print_template()
		 *
		 * @since 4.3.0
		 */
		protected function old_render_template() {
			?>
			<li id="accordion-panel-{{ data.id }}" class="accordion-section control-section control-panel control-panel-{{ data.type }}">
				<h3 class="legacy accordion-section-title<# if ( ! _.isEmpty( data.icon ) ) { #> {{ data.icon }}<# } #>" tabindex="0">
					{{ data.title }}
					<span class="screen-reader-text"><?php esc_html_e( 'Press return or enter to open this panel', 'crio' ); ?></span>
				</h3>
				<ul class="accordion-sub-container control-panel-content"></ul>
			</li>
			<?php
		}
	}
}
