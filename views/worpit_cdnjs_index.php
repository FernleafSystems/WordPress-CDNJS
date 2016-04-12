<?php 
	include_once( dirname(__FILE__).'/widgets/widgets.php' );
?>

<div class="wrap">
	<div class="bootstrap-wpadmin">

		<div class="page-header">
			<a href="http://www.icontrolwp.com/"><div class="icon32" id="worpit-icon"><br /></div></a>
			<h2><?php _worpit_cdnjs_e( 'Dashboard :: CDNJS Plugin from iControlWP' ); ?></h2><?php _worpit_cdnjs_e( '' ); ?>
		</div>
		
		<div class="row" id="tbs_docs">
		  <div class="span6" id="tbs_docs_shortcodes">
			  <div class="well">
				<h3>About CDNJS</h3>
				<p><a href="http://www.icontrolwp.com/" target="_blank">iControlWP</a> didn't make the CDNJS - we only created the WordPress plugin that lets you use the library!</p>
				<p>CDNJS is a freely available CDN repository of Javascript and CSS libraries. They have been made
					available by the team at <a href="http://cdnjs.com" target="_blank">CDNJS.com</a> and CloudFlare to provide you with the fastest available
					CDN for these resources.</p>
				<p>To learn more, read the article at <a href="http://blog.cloudflare.com/cdnjs-the-fastest-javascript-repo-on-the-web" target="_blank">CloudFlare.com here</a>.</p>
		  </div>
		  </div><!-- / span6 -->
		  <div class="span6" id="tbs_docs_examples">
		  </div><!-- / span6 -->
		</div><!-- / row -->

		<?php include_once( dirname(__FILE__).'/widgets/common_widgets.php' ); ?>

		<div class="row" id="worpit_promo">
		  <div class="span12">
		  	<?php echo getWidgetIframeHtml('dashboard-widget-worpit'); ?>
		  </div>
		</div><!-- / row -->

		<div class="row" id="developer_channel_promo">
		  <div class="span12">
		  	<?php echo getWidgetIframeHtml('dashboard-widget-developerchannel'); ?>
		  </div>
		</div><!-- / row -->
		
		<div class="row">
		  <div class="span6">
		  </div><!-- / span6 -->
		  <div class="span6">
		  	<p></p>
		  </div><!-- / span6 -->
		</div><!-- / row -->
		
	</div><!-- / bootstrap-wpadmin -->

</div><!-- / wrap -->