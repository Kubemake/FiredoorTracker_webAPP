		</div>
	</div>
</div>
<div class="container-fluid">
	<div id="footer">
		<div class="container-fluid">
			<p class="text-right">Better Call Saul 555-55-55, saul@law.yer</p>
		</div>
	</div>
</div>
<div id="modalacceptor"></div>
<div id="throbber"></div>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script type="text/javascript" src="/js/bootstrap.min.js"></script>
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
</body>
</html>