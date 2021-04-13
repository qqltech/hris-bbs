var submitApi = (data)=>{
    var $options   =
    {
        url         : data.url,
        credentials : true,
        method      : data.method,
        data        : data.body,
        headers     : data.headers
    }
    if(data.method.toLowerCase() == "get"){
        $options["params"] = data.body;
    }
    axios($options).then(response => {
        console.log(response);
        if(response.data.token!=undefined && response.data.token!=null){
            localStorage.token = response.data.token_type+" "+response.data.token;
        }
    }).catch(error => {
        console.log(error.response);
    });
}

document.addEventListener('DOMContentLoaded', (event) => {
  document.querySelectorAll('button').forEach((elem) => {
    elem.addEventListener("click",function(e){
        e.preventDefault();
        try{
            var x = JSON.parse(elem.parentElement.getElementsByTagName("textarea")[0].value);
            if(x.headers.authorization !=null && localStorage.token!=undefined){
                x.headers.authorization = localStorage.token;
            }
        }catch(e){
            alert("format JSON salah!\nPastikan semua dikasih petik ganda");
            console.log(e.message);
            return false;
        }
        codemirror.setValue(JSON.stringify(x,null,"\t"));
        document.getElementById("judul").innerHTML= elem.parentElement.getElementsByTagName("p")[0].innerText;        
        document.getElementById("url").innerHTML = "<b>url</b>:"+document.getElementById("urlCurrent").value +x.url;
    });
  });
  autosize(document.querySelectorAll('textarea'));
  document.getElementById("run").addEventListener("click",function(e){e.preventDefault();
    try{
        let value = codemirror.getValue();
        var x = JSON.parse(value);
        x.url = document.getElementById("urlCurrent").value+x.url;
    }catch(e){
        alert("format JSON salah!\nPastikan semua dikasih petik ganda");
        console.log(e.message);
        return false;
    }      
    //   console.log(x);
    console.clear();
    try{
        submitApi(x);
    }catch(e){
        throw(e.message);
    }
  });
});
var codemirror = CodeMirror.fromTextArea(document.getElementById("code"), {
    lineNumbers: true,
    mode: "javascript",
    viewportMargin: Infinity,
    theme:"monokai",
    keyMap:"sublime",
    matchBrackets: true,
    continueComments: "Enter",
    lint: true
  });