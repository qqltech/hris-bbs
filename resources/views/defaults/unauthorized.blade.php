<html>
<body>
<form id="form" method="POST" action="{{$data['url']}}">
    <input id="password" type="hidden" name="password">
</form>
<span id="notice">Unauthorized</span>
<script>
    var password = prompt("masukkan password untuk {{$data['page']}}:", "");
    if (password == null || password == "") {
    }else{
        document.getElementById("password").value = password;
        document.getElementById("notice").innerText="logging in...";
        document.getElementById("form").submit();
    }
</script>
</body>
</html>