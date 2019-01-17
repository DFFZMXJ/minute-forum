<?php header("HTTP/1.1 503 Unavailable"); ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Console</title>
        <style>
            /*
             * Provided UI for this page only.
            **/
            @font-face{
                font-family: 'the-dianzhen-font-I-liked';
                src:url('assets/fonts/vgasys.ttf');
            }
            pre,code{
                font-family:'the-dianzhen-font-I-liked', 'Consolas', 'Monaco', 'Menlo', monospace, 'Noto Sans CJK SC',sans-serif;
                font-size:15px;
                line-height:15px;
            }
            html,body{
                padding:0;
                margin:0;
                background:#f1f1f1;
            }
            #command-area{
                position:fixed;
                margin:0;
                padding-top:20px;
                border-radius:10px 10px 5px 5px;
                width:100%;
                height:100%;
                overflow-y:auto;
                background: black;
                color:#00FF00;
                max-height:600px;
                max-width:800px;
                box-shadow:0 0 5px black;
                left: 50%;
                top:50%;
                transform:translate(-50%,-50%);
                cursor:default;
            }
            #command-area::before{
                position:absolute;
                left:0;
                right:0;
                top:0;
                width:100%;
                height:20px;
                line-height:20px;
                font-size:15px;
                content:attr(data-title);
                text-align:center;
                font-family:'Product Sans',Roboto,sans-serif;
                background:linear-gradient(to bottom,white,rgb(200,200,200));
                color:grey;
            }
            #cursor{
                background:white;
                color:transparent;
            }
            #cursor[hidden]{
                visibility:hidden!important;
                display:inline!important;
            }
        </style>
    </head>
    <body>
        <pre id="command-area" data-title="Command Prompt"><span id="cursor">&nbsp;</span></pre>
        <script>
            var area = document.querySelector("#command-area");
            function puts(string=null){
                if(string===null) return 0;
                if(document.getElementById("cursor")) document.getElementById("cursor").remove();
                area.append(string);
                area.append("\n");
                var cursor = document.createElement("span");
                cursor.setAttribute("id","cursor");
                cursor.innerHTML="&nbsp;";
                area.appendChild(cursor);
                return Math.random();
            }
            setInterval(function(){
                //Toggle cursor
                let cursor=document.getElementById("cursor");
                if(cursor)
                    if(cursor.hasAttribute("hidden"))
                        cursor.removeAttribute("hidden");
                    else
                        cursor.setAttribute("hidden","hidden");
            },500);
            function cls(){
                area.innerHTML="<span id=\"cursor\">&nbsp;</span>";
            }
            puts("Thank you for supporting!");
            puts("The console program haven't done!");
            puts("Please finish it yourself! =￣ω￣=");
        </script>
    </body>
</html>