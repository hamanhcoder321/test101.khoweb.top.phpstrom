<script>
	var menus = {
		"oneThemeLocationNoMenus" : "",
		"moveUp" : "Lên một bậc",
		"moveDown" : "Xuống một bậc",
		"moveToTop" : "Lên trên cùng",
		"moveUnder" : "Move under of %s",
		"moveOutFrom" : "Ra khỏi  %s",
		"under" : "Dưới %s",
		"outFrom" : "Ra khỏi %s",
		"menuFocus" : "%1$s. Menu thành phần %2$d hoặc %3$d.",
		"subMenuFocus" : "%1$s. Menu cấp dưới %2$d hoặc %3$s."
	};
	var arraydata = [];     
	var addcustommenur= '{{ route("haddcustommenu") }}';
	var updateitemr= '{{ route("hupdateitem")}}';
	var generatemenucontrolr= '{{ route("hgeneratemenucontrol") }}';
	var deleteitemmenur= '{{ route("hdeleteitemmenu") }}';
	var deletemenugr= '{{ route("hdeletemenug") }}';
	var createnewmenur= '{{ route("hcreatenewmenu") }}';
	var csrftoken="{{ csrf_token() }}";
	var menuwr = "{{ url()->current() }}";

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': csrftoken
		}
	});
</script>
<script type="text/javascript" src="{{asset('public/vendor/harimayco-menu/scripts.js')}}"></script>
<script type="text/javascript" src="{{asset('public/vendor/harimayco-menu/scripts2.js')}}"></script>
<script type="text/javascript" src="{{asset('public/vendor/harimayco-menu/menu.js')}}"></script>