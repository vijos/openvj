<!DOCTYPE html>
<html>
<head>
    <title>{{ q($title) }} - {{ q($title_suffix) }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link href="{{ q($tpl_dir) }}/style.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<script type="text/javascript">
    <!--

    function MM_findObj(n, d) { //v4.01
        var p, i, x;
        if (!d) d = document;
        if ((p = n.indexOf("?")) > 0 && parent.frames.length) {
            d = parent.frames[n.substring(p + 1)].document;
            n = n.substring(0, p);
        }
        if (!(x = d[n]) && d.all) x = d.all[n];
        for (i = 0; !x && i < d.forms.length; i++) x = d.forms[i][n];
        for (i = 0; !x && d.layers && i < d.layers.length; i++) x = MM_findObj(n, d.layers[i].document);
        if (!x && d.getElementById) x = d.getElementById(n);
        return x;
    }

    function MM_showHideLayers() { //v6.0
        var i, p, v, obj, args = MM_showHideLayers.arguments;
        for (i = 0; i < (args.length - 2); i += 3) if ((obj = MM_findObj(args[i])) != null) {
            v = args[i + 2];
            if (obj.style) {
                obj = obj.style;
                v = (v == 'show') ? 'visible' : (v == 'hide') ? 'hidden' : v;
            }
            obj.visibility = v;
        }
        window.document.body.scroll = "no"
        Console.style.top = document.body.scrollTop
    }

    var userAgent = navigator.userAgent;
    if (userAgent.indexOf('MSIE 6') > -1 || userAgent.indexOf('MSIE 7') > -1) {
        document.write('<div class="iewarning">您的浏览器使用的内核已经严重过时了，身为一个OIer应当以身作则更换浏览器。<br>【另：Vijos不排除在未来某天拒绝IE6/7用户的访问的可能。】</div>');
    }

    //!-->
</script>
{{ IF $title == "注册" }}
<style type="text/css">
    <!--
    .mask {
        display: none;
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        z-index: 10;
        background-color: #ccc;
        filter: progid:DXImageTransform.Microsoft.Alpha(opacity=90);
        opacity: .9;
        text-align: center;
    }

    .msgbox {
        display: none;
        z-index: 11;
        border: 10px solid red;
        background-color: #fff;
        margin: 200px auto auto;
        width: 300px;
        padding: 5px;
    }

    -->
</style>

<div class="mask">
    <div class="msgbox style1">
        验证邮件已发送到您的邮箱<br/>
        请注意查收 (有效期24小时)<br/>
        <br/>
        请点击 <a href="."><strong>这里</strong></a> 返回首页
    </div>
</div>


<div class="mask">
    <div class="msgbox style1"><br/>
        注册成功！欢迎加入Vijos!
        <br/><br/>
        请点击 <a href="."><strong>这里</strong></a> 返回首页<br/><br/>
    </div>
</div>

{{ END }}

<div id="pagecnt">
    <div id="header">
        <img src="{{ q($tpl_dir) }}/Head.gif" alt="head"/>

        <div id="browser">
<span class="white">
{{ IF $global_uname }}
您好&nbsp;{$global_uname}&emsp;&emsp;
    通过{$header_pass}题&emsp;/&emsp;提交{$header_submit}次&emsp;({$header_ratio})
    {{ ELSE }}
请点击<a href="."><span class="white bold">这里</span></a>登录
    {{ END }}&emsp;&emsp;
</span>
<span class="style3">
<a href=".">首页</a> <a href="problems">题库</a> <a href="records">记录</a>
<a href="tags">标签</a> <a href="tests">比赛</a>
<a href="discuss">讨论</a>&emsp;|&emsp;<a href="error.php">U-S</a>
<a href="error.php">搜索</a> <a href="skin/vj2">换肤</a>
<a id="StranLink" href="#">正体</a><script type="text/javascript" src="static/Std_StranJF.js"></script>&emsp;|&emsp;{{ IF $user }}<a
            href="user/logout?sid={{ q($global_sid) }}">登出</a>
    {{ ELSE }}
<a href=".">登录</a> <a href=".">注册</a>
    {{ END }}&emsp;
</span>
        </div>
    </div>
    <div id="cnews">
        <div class="section">
            <div class="s_c s_lt"></div>
            <div class="s_c s_rt"></div>
            <div class="s_c s_lb"></div>
            <div class="s_c s_rb"></div>
            <div class="s_v s_t"></div>
            <div class="s_v s_b"></div>
            <div class="s_v s_l"></div>
            <div class="s_v s_r"></div>
            <div class="s_content">
                <marquee id="news" direction="left" scrollamount="4" onMouseOver="this.stop();"
                         onMouseOut="this.start();">
                    <span id="tnews">公告 News &gt;&gt;</span>
                    {{ IF $header_anno }}
                    {{ BEGIN header_anno }}
                    　　<a href="/discuss/{{ q($id) }}"><span class="enews">{{ q($title) }}  ({{ q($time) }})</span></a>
                    {{ END }}
                    {{ ELSE }}
                    <a href="#" class="enews">暂无公告</a>
                    {{ END }}
                </marquee>
            </div>
        </div>
    </div>
    <div class="hr">
        <hr/>
    </div>
    <div id="body">