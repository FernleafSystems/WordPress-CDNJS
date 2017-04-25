<?php
/*
 * Plugin Name: CDNJS for WordPress
 * Plugin URI: http://icwp.io/6x
 * Description: Allows you to easily include Javascript and other resources from CDNJS.
 * Version: 1.3.3
 * Author: iControlWP
 * Author URI: http://icwp.io/6x
*/

/**
 * Copyright (c) 2017 iControlWP <support@icontrolwp.com>
 * All rights reserved.
 *
 * "CDNJS for WordPress" is
 * distributed under the GNU General Public License, Version 2,
 * June 1991. Copyright (C) 1989, 1991 Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110, USA
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

require_once( dirname(__FILE__).'/src/worpit-plugins-base.php' );

function _worpit_cdnjs_e( $insStr ) {
	_e( $insStr, 'worpit-cdnjs' );
}
function _worpit_cdnjs__( $insStr ) {
	return __( $insStr, 'worpit-cdnjs' );
}

if ( !class_exists('ICWP_Cdnjs_Main') ):

class ICWP_Cdnjs_Main extends Worpit_Cdnjs_Base_Plugin {
	
	const InputPrefix				= 'worpit_cdnjs_';
	const OptionPrefix				= 'worpit_cdnjs_'; //ALL database options use this as the prefix.
	const CDNJS_URL = 'http://api.cdnjs.com/libraries';

	static public $VERSION			= '1.3.3'; //SHOULD BE UPDATED UPON EACH NEW RELEASE
	
	static public $aWP_MAPPINGS;

	protected $aPluginOptions_CdnJs_JsIncludes;
	protected $aPluginOptions_CdnJs_CssIncludes;
	protected $m_aPluginOptions_GeneralSection;
	
	public function __construct() {
		parent::__construct();

		register_activation_hook( __FILE__, array( $this, 'onWpActivatePlugin' ) );
		register_deactivation_hook( __FILE__, array( $this, 'onWpDeactivatePlugin' ) );
	//	register_uninstall_hook( __FILE__, array( $this, 'onWpUninstallPlugin' ) );
		
		self::$PLUGIN_NAME	= basename(__FILE__);
		self::$PLUGIN_PATH	= plugin_basename( dirname(__FILE__) );
		self::$PLUGIN_DIR	= WP_PLUGIN_DIR.WORPIT_DS.self::$PLUGIN_PATH.WORPIT_DS;
		self::$PLUGIN_URL	= plugins_url( '/', __FILE__ ) ; //this seems to use SSL more reliably than WP_PLUGIN_URL
		self::$OPTION_PREFIX = self::OptionPrefix;
		
		$this->sPluginSlug = 'cdnjs';
		
		if ( !is_admin() ) {
			add_action( 'init', array( $this, 'enqueueAll' ), 999 );
		}
		
	}//__construct
	
	protected function initPluginOptions() {
		
		$this->aPluginOptions_CdnJs_JsIncludes = array(
			'section_title' => 'Choose Common Javascript Libraries'
		);
		$this->aPluginOptions_CdnJs_CssIncludes = array(
			'section_title' => 'Choose Common CSS Libraries'
		);

		if ( function_exists( 'json_decode' ) ) {
			$this->readAllPackages();
		}
		else {
			$this->aPluginOptions_CdnJs_JsIncludes[ 'section_options'] = array(
				array(	'cufon',				'',		'N', 	'checkbox',	'Cufón', 'Include the latest Cufón JS library', "" ),
				array(	'firebug-lite',			'',		'N', 	'checkbox',	'Firebug Lite', 'Include the Firebug Lite JS library', "" ),
				array(	'jquery',				'',		'N', 	'checkbox',	'JQuery', 'Include the latest JQuery library', "Will replace your WordPress JQuery library." ),
				array(	'jqueryui',				'',		'N', 	'checkbox',	'JQuery UI', 'Include the latest JQuery UI library', "" ),
				array(	'json2',				'',		'N', 	'checkbox',	'JSON 2', 'Include the latest JSON 2 library', "" ),
				array(	'json3',				'',		'N', 	'checkbox',	'JSON 3', 'Include the latest JSON 3 library', "" ),
				array(	'less',					'',		'N', 	'checkbox',	'less.js', 'Include the latest LESS Compiler JS library', "" ),
				array(	'mootools',				'',		'N', 	'checkbox',	'Mootools', 'Include the latest Mootools library', "" ),
				array(	'prettify',				'',		'N', 	'checkbox',	'Prettify', 'Include the latest Prettify library', "" ),
				array(	'twitter-bootstrap',	'',		'N', 	'checkbox',	'Twitter Bootstrap', 'Include the latest Twitter Bootstrap JS library', "Remember to include the CSS below" ),
			);
			$this->aPluginOptions_CdnJs_CssIncludes[ 'section_options'] = array(
				array(	'960gs',					'',	'N', 	'checkbox',	'960gs', 'Include the latest 960gs CSS library', "" ),
				array(	'normalize',				'',	'N', 	'checkbox',	'Normalize', 'Include the latest Normalize library', "" ),
				array(	'font-awesome',				'',	'N', 	'checkbox',	'Font Awesome', 'Include the latest Font Awesome library', "" ),
			);
			$this->m_aPluginOptions_GeneralSection = array(
				'section_title' => 'General CDNJS Plugin Options',
				'section_options' => array( )
			);
			
		}//if-else
		
		$this->aAllPluginOptions = array(
			&$this->aPluginOptions_CdnJs_CssIncludes,
			&$this->aPluginOptions_CdnJs_JsIncludes,
			//			&$this->m_aPluginOptions_GeneralSection
		);
		
		/**
		 * This allows us to replace the WordPress libraries. e.g. CDNJS has jqueryui, but this is registered in WP
		 * as jquery-ui.
		 */
		self::$aWP_MAPPINGS = array(
			'jqueryui'	=>	'jquery-ui'
		);
		
		return true;
	}

	public function onWpAdminInit() {
		parent::onWpAdminInit();
		
		global $pagenow;
		//Loads the news widget on the Dashboard (if it hasn't been disabled)
		if ( $pagenow == 'index.php' ) {
			$sDashboardRssOption = self::getOption( 'hide_dashboard_rss_feed' );
			if ( empty( $sDashboardRssOption ) || $sDashboardRssOption == 'N' ) {
				require_once( dirname(__FILE__).'/src/icwp-rssfeed-widget.php' );
				ICWP_DashboardRssWidget::GetInstance();
			}
		}
		
		//Enqueues the WP Admin Twitter Bootstrap files if the option is set or we're in a Worpit admin page.
		if ( $this->isWorpitPluginAdminPage() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueueBootstrapAdminCss' ), 99 );
		}
		
		//Multilingual support.
		load_plugin_textdomain( 'worpit-cdnjs', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}
	
	protected function createPluginSubMenuItems(){
		$this->m_aPluginMenu = array(
			//Menu Page Title => Menu Item name, page ID (slug), callback function for this page - i.e. what to do/load.
			$this->getSubmenuPageTitle( 'CDNJS' ) => array( 'Includes', $this->getSubmenuId( 'includes' ), 'onDisplayCdnjsIncludes' ),
		);
	}
	
	protected function handlePluginUpgrade() {
		
		//current_user_can( 'manage_options' ) ensure only valid users attempt this.
		if ( self::getOption( 'current_plugin_version' ) !== self::$VERSION && current_user_can( 'manage_options' ) ) {

			//Set the flag so that this update handler isn't run again for this version.
			self::updateOption( 'current_plugin_version', self::$VERSION );
		}

		//Someone clicked the button to acknowledge the update
		if ( isset( $_POST['hlt_hide_update_notice'] ) && isset( $_POST['hlt_user_id'] ) ) {
			update_user_meta( $_POST['hlt_user_id'], self::OptionPrefix.'current_version', self::$VERSION );
			header( "Location: admin.php?page=".$this->getFullParentMenuId() );
		}
		
	}
	
	public function onWpAdminNotices() {
		
		//Do we have admin priviledges?
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}
		
		$this->adminNoticeVersionUpgrade();
		$this->adminNoticeOptionsUpdated();
	}
	
	private function adminNoticeVersionUpgrade() {

		global $current_user;
		$user_id = $current_user->ID;

		$sCurrentVersion = get_user_meta( $user_id, self::OptionPrefix.'current_version', true );

		if ( $sCurrentVersion !== self::$VERSION ) {
			$sNotice = '
					<style>
						a#fromWorpit {
							padding: 0 5px;
							border-bottom: 1px dashed rgba(0,0,0,0.1);
							color: black;
						}
					</style>
					<form method="post" action="admin.php?page=worpit-cdnjs">
						<p><strong>CDNJS Plugin, <a href="http://bit.ly/QhYJzY" id="fromWorpit" title="Manage WordPress Better" target="_blank">from iControlWP</a></strong> has been updated successfully.
						<input type="hidden" value="1" name="hlt_hide_update_notice" id="hlt_hide_update_notice">
						<input type="hidden" value="'.$user_id.'" name="hlt_user_id" id="hlt_user_id">
						<input type="submit" value="Okay, take me to the main plugin page and hide this notice" name="submit" class="button-primary">
						</p>
					</form>
			';

			$this->getAdminNotice( $sNotice, 'updated', true );
		}
		
	}
	
	private function adminNoticeOptionsUpdated() {
		
		$sAdminFeedbackNotice = $this->getOption( 'feedback_admin_notice' );
		if ( !empty( $sAdminFeedbackNotice ) ) {
			$sNotice = '<p>'.$sAdminFeedbackNotice.'</p>';
			$this->getAdminNotice( $sNotice, 'updated', true );
			$this->updateOption( 'feedback_admin_notice', '' );
		}
	}
	
	public function onDisplayCdnjsIncludes() {
		
		//populates plugin options with existing configuration
		$aAllIncludes = array();
		$aIncludes = $this->getOption( 'includes' );
		if ( $aIncludes !== false ) {
			foreach( $aIncludes as $sInclude => $aData ) {
				list( $sVersion, $sFilename ) = $aData;
				$sComplete = implode( '_SEP_', array( self::$OPTION_PREFIX.$sInclude, $sVersion, $sFilename ) );
				$sComplete = str_replace( '.', '_DOT_', $sComplete);
				$aAllIncludes[ $sComplete ] = 'Y';
			}
		}
		$this->populateAllPluginOptions( $aAllIncludes );

		//Specify what set of options are available for this page
		$aAvailableOptions = array(
			&$this->aPluginOptions_CdnJs_CssIncludes,
			&$this->aPluginOptions_CdnJs_JsIncludes
		);
		
		$aData = array(
			'plugin_url'		=> self::$PLUGIN_URL,
			'var_prefix'		=> self::OptionPrefix,
			'aAllOptions'		=> $aAvailableOptions,
			'nonce_field'		=> $this->getSubmenuId( 'includes' ),
			'form_action'		=> 'admin.php?page='.$this->getSubmenuId( 'includes' )
		);

		$this->display( $this->getSubmenuId('includes'), $aData );
		
	}
	
	protected function readAllPackages() {

		$sQuery = add_query_arg(
			array(
				'fields' => 'name,version,description,filename'
			),
			self::CDNJS_URL
		);
		$aResponse = wp_remote_get( $sQuery ); // may generate warning, see: http://wordpress-hackers.1065353.n5.nabble.com/gzinflate-Warning-on-transient-expiration-td40201.html
		if ( is_wp_error( $aResponse ) || !isset( $aResponse['response']['code'] ) || $aResponse['response']['code'] != 200 || empty( $aResponse['body'] ) ) {
			return;
		}
		$oAllPackages = json_decode( $aResponse['body'] );
		$aPackages = $oAllPackages->results;

		$this->aPluginOptions_CdnJs_JsIncludes[ 'section_options'] = array();
		$this->aPluginOptions_CdnJs_CssIncludes[ 'section_options'] = array();

		$aAcceptableExtensions = array( 'css', 'js' );
		foreach( $aPackages as $oPackage ) {

			$aFileNameParts = explode( '.', $oPackage->filename );
			$sExtension = strtolower( $aFileNameParts[ count( $aFileNameParts ) - 1 ] );

			if ( in_array( $sExtension, $aAcceptableExtensions ) ) {
				$sArrayToAddTo = sprintf( 'aPluginOptions_CdnJs_%sIncludes', ucfirst( $sExtension ) );
				$this->{$sArrayToAddTo}[ 'section_options' ][] = array(
					str_replace( '.', '_DOT_', $oPackage->name.'_SEP_'.$oPackage->version.'_SEP_'.$oPackage->filename ),
					'', 'N', 'checkbox',
					sprintf( '%s<br /><small>(v%s)</small>', $oPackage->name, $oPackage->version ),
					sprintf(
						'Add the <strong>%s</strong> %s Library',
						$oPackage->name,
						strtoupper( $sExtension )
					),
					esc_attr( $oPackage->description ),
					$oPackage->version
				);
			}
		}
		
		//Not entirely necessary, but makes the interface a little better
		sort( $this->aPluginOptions_CdnJs_CssIncludes[ 'section_options'] );
		sort( $this->aPluginOptions_CdnJs_JsIncludes[ 'section_options'] );
	}
	
	protected function handlePluginFormSubmit() {
		
		if ( !isset( $_POST['worpit_plugin_form_submit'] ) ) {
			return;
		}
		
		$this->m_fSubmitCbcMainAttempt = true;
	
		if ( isset( $_GET['page'] ) ) {
			switch ( $_GET['page'] ) {
				case $this->getSubmenuId( 'includes' ):
					$this->handleSubmit_CdnjsIncludes();
					break;
			}
		}
		
		if ( !self::$m_fUpdateSuccessTracker ) {
			self::updateOption( 'feedback_admin_notice', 'Updating Settings <strong>Failed</strong>.' );
		}
		else {
			self::updateOption( 'feedback_admin_notice', 'Updating Settings <strong>Succeeded</strong>.' );
		}
		
	}//handlePluginFormSubmit
	
	protected function handleSubmit_CdnjsIncludes() {

		//Ensures we're actually getting this request from WP.
		check_admin_referer( $this->getSubmenuId('includes') );
		
		// Will store values serialized
		// First remove unnecessary POST values.
		unset( $_POST['_wpnonce'] );
		unset( $_POST['_wp_http_referer'] );
		unset( $_POST['worpit_plugin_form_submit'] );
		unset( $_POST['submit'] );
		unset( $_POST[self::OptionPrefix.'all_options_input'] );
		
		$aOptions = array();
		foreach( $_POST as $sIncludeName => $val ) {
			$sIncludeName = str_replace( self::OptionPrefix, '', $sIncludeName );
			$sIncludeName = str_replace( '_DOT_', '.', $sIncludeName);
			list($sName, $sVersion, $sFileName) = explode( '_SEP_', $sIncludeName );
			$aOptions[ $sName ] = array( $sVersion, $sFileName );
		}

		self::updateOption( 'includes', $aOptions );

	}

	public function enqueueAll() {
		
		$aAllIncludes = $this->getOption( 'includes' );
		
		//No includes have been set yet.
		if ( empty($aAllIncludes) ) {
			return;
		}

		//Requires WordPress 3.5+
		$sUrlStem = '//cdnjs.cloudflare.com/ajax/libs/';
		
		foreach( $aAllIncludes as $sIncludeName => $aDetails ) {
			
			list( $sVersion, $sFileName ) = $aDetails;
			
			//Ensure we actually replace WordPress libraries.
			if ( isset(self::$aWP_MAPPINGS[ $sIncludeName ]) ) {
				$sWpName = self::$aWP_MAPPINGS[ $sIncludeName ];
			}
			else {
				$sWpName = $sIncludeName;
			}

			$this->enqueueResource( $sWpName, $sUrlStem.$sIncludeName.'/'.$sVersion.'/'.$sFileName, $sVersion );

			// if it is bootstrap, also enqueue the CSS
			if ( $sIncludeName == 'twitter-bootstrap' ) {
				$this->enqueueResource( $sIncludeName.rand(), $sUrlStem.$sIncludeName.'/'.$sVersion.'/css/bootstrap.min.css', $sVersion );
				$this->enqueueResource( $sIncludeName.rand(), $sUrlStem.$sIncludeName.'/'.$sVersion.'/css/bootstrap-responsive.min.css', $sVersion );
			}
		}
		
	}
	
	public function enqueueResource( $insResourceName, $insResourceSource, $insVersion ) {
		
		if ( substr($insResourceSource, -3) == '.js' ) {
			wp_deregister_script( $insResourceName );
			wp_register_script( $insResourceName, $insResourceSource, false, $insVersion );
			wp_enqueue_script( $insResourceName );
			
		}
		elseif ( substr($insResourceSource, -4) == '.css' ) {
			wp_deregister_style( $insResourceName );
			wp_register_style( $insResourceName, $insResourceSource, false, $insVersion );
			wp_enqueue_style( $insResourceName );
		}
	}

		/**
		 * @param $sHandle
		 * @return bool
		 */
	protected function isRegistered( $sHandle ) {
		return (
			wp_script_is( $sHandle, 'registered' ) ||
			wp_script_is( $sHandle, 'queue' ) ||
			wp_script_is( $sHandle, 'done' ) ||
			wp_script_is( $sHandle, 'to_do' )
		);
	}
	
	public function onWpPluginActionLinks( $inaLinks, $insFile ) {
		if ( $insFile == plugin_basename( __FILE__ ) ) {
			$sSettingsLink = '<a href="'.admin_url( "admin.php" ).'?page='.$this->getSubmenuId('includes').'">' . _worpit_cdnjs__( 'Settings' ) . '</a>';
			array_unshift( $inaLinks, $sSettingsLink );
		}
		return $inaLinks;
	}
	
	protected function deleteAllPluginDbOptions() { }
	
	public function onWpDeactivatePlugin() {
		
		if ( $this->getOption('delete_on_deactivate') == 'Y' ) {
			$this->deleteAllPluginDbOptions();
		}
		
		$this->deleteOption( 'current_plugin_version' );
		$this->deleteOption( 'feedback_admin_notice' );
	}
	
	public function onWpActivatePlugin() { }
	
	public function enqueueBootstrapAdminCss() {
		wp_register_style( 'worpit_bootstrap_wpadmin_css', $this->getCssUrl('bootstrap-wpadmin.css'), false, self::$VERSION );
		wp_enqueue_style( 'worpit_bootstrap_wpadmin_css' );
		wp_register_style( 'worpit_bootstrap_wpadmin_css_fixes', $this->getCssUrl('bootstrap-wpadmin-fixes.css'),  array('worpit_bootstrap_wpadmin_css'), self::$VERSION );
		wp_enqueue_style( 'worpit_bootstrap_wpadmin_css_fixes' );
	}
	
}

endif;

$oWorpit_Cdnjs_Main = new ICWP_Cdnjs_Main();
