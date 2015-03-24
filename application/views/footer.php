		</div>
	</div>
</div>
<div id="modalacceptor"></div>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script type="text/javascript" src="/js/bootstrap.min.js"></script>
<?=@$scripts?>
<script type="text/javascript">
	$(document).ready(function() {
		ttl = location.href.replace('http://'+location.host,'');
		
		$('ul.navbar-nav li').each(function(){
			lival = $(this).find('a');
			if (ttl.indexOf(lival.attr('href'))==0 && lival.attr('href')!=='/') {
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