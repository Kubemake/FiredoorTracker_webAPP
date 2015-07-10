		</div>
	</div>
</div>
<div class="container-fluid">
	<div id="footer">
		<div class="container-fluid">
			<p class="text-right">For help, please call 844.524.1212</p>
		</div>
	</div>
</div>
<div id="modalacceptor"></div>
<div id="warnacceptor"></div>
<div id="throbber"></div>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script type="text/javascript" src="/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/bootstrap-select/bootstrap-select.js"></script>

<?=@$scripts?>

<script type="text/javascript">
	$(document).ready(function() {
		ttl = location.href.replace('http://'+location.host,'');
		if (ttl=='/admin/clients') 
			ttl='/user/clients';
		$('ul.navbar-nav li').each(function(){
			lival = $(this).find('a').attr('href');

			if (lival=='/user/profile') 
				lival='/user';

			if (ttl.indexOf(lival)==0 && lival!=='/') {
				$(this).addClass('active');
			}
		});
		
		if (ttl=='/') {
			$('ul.navbar-nav li.first').addClass('active');
		}
	}) 
</script>

<?php
	$licwarn = $this->session->flashdata('showlicwarn');

	if (!empty($licwarn) && $this->session->userdata('user_role') == 1)
	{
		
		$day = ' days';
		if ($licwarn == 1)
			$day = ' day';

		$warn['title'] = 'Important message about your license expiration';
		$warn['text']  = 'Please note your license expires on ' . $licwarn[1] . $day . '. <br>
						  Please click "Renew Now" to renew your license for same terms and conditions<br>
						  If you have any questions, please call us at 844.524.1212, or visit our website at <a href="https://www.firedoortracker.com">www.firedoortracker.com</a> for more assistance<br>
						  Thank you';
		$warn['actionbutton'] = '<a target="_blank" onclick="$(\'#ShowWarnModal\').modal();" href="https://www.firedoortracker.com/pricing" class="btn btn-primary">Renew Now</a>';
		$warn['canselbutton'] = '<button type="button" class="btn btn-default" data-dismiss="modal">OK</button>';

		$this->load->view('modal/show_warn_modal', $warn);
		
		?><script type="text/javascript">
			$(function(){
				$('#ShowWarnModal').modal({show: true});
			})
		</script><?php
	}
?>
</body>
</html>