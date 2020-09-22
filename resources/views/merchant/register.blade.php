@extends('layouts.apps')

@section('css')
<style type="text/css">

.weui-icon_toast.weui-loading{margin: .8rem 0 0;}

.weui-toast{width: 6rem; height: 5.5rem; min-height: 5.5rem; top: 33%; left: 45%}

.weui-icon_toast{font-size: 2rem; margin-bottom: .6rem}

.weui-toast_content{ font-size: .7rem; }



.weui-vcode-btn{ color: #763a8a; }

.page_logo{ width: 50%; height: auto; }


.weui-input{text-align: right;}
.page2{ display: none; }

.weui-cells{margin-top: 0; line-height: 1.87}
.weui-cells_radio .weui-check:checked+.weui-icon-checked:before{color: #6C75FF}
.weui-agree__checkbox:checked:before{color: #6C75FF}
.toolbar .picker-button, .fa-calendar{color: #6C75FF}
.date{width: 37%; padding-left: 2%; text-align: center;}
.weui-btn-area{margin: 0; position: fixed; bottom: 0; width: 100%}
.weui-btn_primary{ background: linear-gradient(to right, #6C75FF, #6C55FF); width: 100%; border-radius: 0; }

/**
 * 放大镜
 */
.red {color: #faddde;border: solid 1px #980c10;background: #d81b21;background: -webkit-gradient(linear, left top, left bottom, from(#ed1c24), to(#A51715));background: -moz-linear-gradient(top,  #ed1c24,  #A51715);filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ed1c24', endColorstr='#aa1317');
}
.red:hover { background: #b61318; background: -webkit-gradient(linear, left top, left bottom, from(#c9151b), to(#a11115)); background: -moz-linear-gradient(top,  #c9151b,  #a11115); filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#c9151b', endColorstr='#a11115'); color:#fff;}
.red:active {color: #de898c;background: -webkit-gradient(linear, left top, left bottom, from(#aa1317), to(#ed1c24));background: -moz-linear-gradient(top,  #aa1317,  #ed1c24);filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#aa1317', endColorstr='#ed1c24');}
.cor_bs,.cor_bs:hover{color:#ffffff;}
.keBody{background:url(../images/bodyBg.jpg) repeat #333;}
.keTitle{height:100px; line-height:100px; font-size:30px; font-family:'微软雅黑'; color:#FFF; text-align:center; background:url(../images/bodyBg3.jpg) repeat-x bottom left; font-weight:normal}
.kePublic{background:#FFF; padding:50px;}
.keBottom{color:#FFF; padding-top:25px; line-height:28px; text-align:center; font-family:'微软雅黑'; background:url(../images/bodyBg2.jpg) repeat-x top left; padding-bottom:25px}
.keTxtP{font-size:16px; color:#ffffff;}
.keUrl{color:#FFF; font-size:30px;}
.keUrl:hover{ text-decoration: underline; color: #FFF; }
.mKeBanner,.mKeBanner div{text-align:center;}
/*科e互联特效基本框架CSS结束，应用特效时，以上样式可删除*/
#bigGlass{height:45px;position:absolute;background:#6C75FF;display:none;line-height:45px;font-size:30px;font-weight:bold;padding:0 5px;}
#bigGlass span{margin-left:8px; color:white; font-size: 22px;}
#bigGlass span:first-child{margin-left:0;}
</style>
@endsection

@section('content')

<header class="demos-header" style="text-align: center; padding: 50px auto; color: green">
    <image class="page_logo" src="{{ asset('images/logo.png') }}" />
</header>

<form action="" method="post" name="register" id="register_form">
@csrf
<div class="page page1">

	<div class="weui-cells__title">商户基本资料</div>

	<div class="weui-cells weui-cells_form">

		<div class="weui-cells">
			<a class="weui-cell weui-cell_access" href="javascript:;">
		        <div class="weui-cell__hd"><label for="merTypeRadio" class="weui-label">商户类型</label></div>
		        <div class="weui-cell__bd">
		        	<input class="weui-input" id="merTypeRadio" name="merTypeRadio" type="text" value="小微商户" data-values="2" readonly>
		        </div>
		        <div class="weui-cell__ft"></div>
		    </a>
	    </div>


		<div class="weui-cells">
			<a class="weui-cell weui-cell_access" href="javascript:;">
		        <div class="weui-cell__hd"><label for="area" class="weui-label">商户地址</label></div>
		        <div class="weui-cell__bd">
		          	<input class="weui-input" id="area" name="area" type="text" value="山东省 济南市 天桥区" readonly>
		        </div>
		        <div class="weui-cell__ft"></div>
	    	</a>
	    </div>


	    <div class="weui-cell">
	        <div class="weui-cell__hd"><label for="address" class="weui-label">详细地址</label></div>
	        <div class="weui-cell__bd">
	          	<input class="weui-input" id="address" placeholder="您营业的详细地址" name="address" type="text" value="">
	        </div>
	    </div>

	</div>


	<div class="weui-cells__title">商户身份信息</div>

	<div class="weui-cells weui-cells_form">

	    <div class="weui-cell">
	        <div class="weui-cell__hd"><label for="owenname" class="weui-label">法人姓名</label></div>
	        <div class="weui-cell__bd">
	          	<input class="weui-input" id="owenname" placeholder="请输入商户的法人" name="owenname" type="text" value="">
	        </div>
	    </div>

	    <div class="weui-cell">
	        <div class="weui-cell__hd"><label for="phone" class="weui-label">手 机 号</label></div>
	        <div class="weui-cell__bd">
	          	<input class="weui-input" id="phone" placeholder="请输入您的手机号" name="phone" type="number" value="">
	        </div>
	    </div>

	    <div class="weui-cell">
	        <div class="weui-cell__hd"><label for="idcard" class="weui-label">身份证号</label></div>
	        <div class="weui-cell__bd">
	          	<input class="weui-input" id="idcard" placeholder="请输入您的身份证号" name="idcard" type="text" value="">
	        </div>
	    </div>

	    <div class="weui-cell">
	        <div class="weui-cell__hd"><label for="expd" class="weui-label">有效期限</label></div>
	        <div class="weui-cell__bd">
	        	<i class="fa fa-calendar"></i>
	          	<input class="weui-input date" placeholder="有效期:起" id="exps" name="expds" type="text" value="" readonly>
	          	-
	          	<i class="fa fa-calendar"></i>
	          	<input class="weui-input date" placeholder="有效期:止" id="expd" name="expde" type="text" value="" readonly>
	        </div>
	    </div>

	</div>

	<div class="weui-btn-area">
    	<button type="button" class="weui-btn weui-btn_primary" id="next">下一步</button>
	</div>
</div>

<div class="page page2">

	<div class="weui-cells weui-cells_form">

		<div class="weui-cells__title">商户结算信息</div>

	    <div class="weui-cell">
	        <div class="weui-cell__hd"><label for="username" class="weui-label">结算户名</label></div>
	        <div class="weui-cell__bd">
	          	<input class="weui-input" id="username" name="username" type="text" value="">
	        </div>
	    </div>

		<div class="weui-cells">
			<a class="weui-cell weui-cell_access" href="javascript:;">
		        <div class="weui-cell__hd"><label for="bankname" class="weui-label">结算银行</label></div>
		        <div class="weui-cell__bd">
		        	<input class="weui-input" placeholder="请选择结算银行" id="bankname" name="bankname" type="text" readonly>
		        </div>
		        <div class="weui-cell__ft"></div>
		    </a>
	    </div>


	    <div class="weui-cell">
	        <div class="weui-cell__hd"><label for="bankaccount" class="weui-label">银行卡号</label></div>
	        <div class="weui-cell__bd">
	          	<input class="weui-input" placeholder="请输入您的结算卡账号" id="bankaccount" name="bankaccount" type="text">
	        </div>
	    </div>


	    <div class="weui-cells__title">绑定机具信息</div>
	    <div class="weui-cell">
	        <div class="weui-cell__hd"><label for="temial" class="weui-label">终端编号</label></div>
	        <div class="weui-cell__bd">
	          	<input class="weui-input" placeholder="请输入终端设备的SN编号(全)" id="temial" name="temial" type="text" value="">
	        </div>
	    </div>



		<label for="weuiAgree" class="weui-agree">
		      <input id="weuiAgree" type="checkbox" class="weui-agree__checkbox">
		      <span class="weui-agree__text">
		        阅读并同意<a href="javascript:void(0);">{{ config('app.name', '畅伙伴') }}《相关条款》</a>
		      </span>
		</label>

		<div class="weui-btn-area">
	    	<button class="weui-btn weui-btn_primary">立即注册</button>
		</div>

	</div>
</div>
</form>
@endsection

@section('javascript')
<script type="text/javascript" src="{{ asset('js/bigGlass.js') }}"></script>

<script type="text/javascript">
@if(count($errors) > 0)
    $.toptip('{{ $errors->first() }}', 'error');
@endif

/**
 * 商户类型选择
 * @type {String}
 */
$("#merTypeRadio").select({
  	title: "选择商户类型",
  	items: [
	    {
	      title: "小微商户",
	      value: 2,
	    },{
	      title: "企业(有营业执照)",
	      value: 0,
	    },{
	      title: "个体(有营业执照)",
	      value: 1,
	    }
  	]
});

/**
 * @version 结算银行
 */
$("#bankname").select({
  	title: "选择结算银行",
  	items: ["中国工商银行", "中国农业银行", "招商银行", "邮政储蓄银行", "中国建设银行", "中国银行", "浦发银行", "中信银行"]
});


/**
 * @version 商户地址选
 */
$("#area").cityPicker({
    title: "请选择商户地址"
});

/**
 * 日历
 */
$("#exps").calendar({
	minDate: "2000-01-01",
	maxDate: getCurrentDate(),
	dateFormat: 'yyyy-mm-dd',
});
$("#expd").calendar({
	minDate: getCurrentDate(),
	maxDate: "2040-01-01",
	dateFormat: 'yyyy-mm-dd',
});
/**
 * @version 下一步
 */
$("#next").click(function(){
	$(".page1").css({"display": "none"});
	$(".page2").css({"display": "block"});
})


/**
 * 获取当前日期
 */
function getCurrentDate(){
	var myDate = new Date;
    var year = myDate.getFullYear(); //获取当前年
    var mon = myDate.getMonth() + 1; //获取当前月
    var date = myDate.getDate(); //获取当前日
    return year+"-"+mon+"-"+date;
}

/**
 * 放大镜效果
 */

$("#idcard").bigGlass(1);
$("#phone").bigGlass(2);
</script>
@endsection

