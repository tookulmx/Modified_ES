<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
#wrapper {
	background-color: #f5f5f5;
	margin: 0;
	padding: 70px 0 70px 0;
	-webkit-text-size-adjust: none !important;
	width: 100%;
}

#template_container {
	box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important;
	background-color: #fdfdfd;
	border: 1px solid #dcdcdc;
	border-radius: 3px !important;
}

#template_header {
	background-color: #499f90;
	border-radius: 3px 3px 0 0 !important;
	color: #ffffff;
	border-bottom: 0;
	font-weight: bold;
	line-height: 100%;
	vertical-align: middle;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

#template_header h1,
#template_header h1 a {
	color: #ffffff;
}

#template_footer {
	padding: 3em 0 0;
	border: 1px solid #eeeeee;
}
#template_footer td {
    padding: 0;
    -webkit-border-radius: 6px;
}

#template_footer #credit {
	border:0;
	color: #99b1c7;
	font-family: Arial;
	font-size:12px;
	line-height:125%;
	text-align:center;
	padding: 0 48px 48px 48px;
}

#body_content {
	background-color: #fdfdfd;
}

#body_content table td {
	padding: 48px;
}

#body_content table td td {
	padding: 12px;
}

#body_content table td th {
	padding: 12px;
}

#body_content p {
	margin: 0 0 16px;
}

#body_content_inner {
	color: #737373;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
	font-size: 14px;
	line-height: 150%;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

.td {
	color: #737373;
	border: 1px solid #e4e4e4
}

.text {
	color: #505050;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

.link {
    color: #557da1;
}

.item-meta {
    font-size: 10px;
    margin-top: 0.5em;
    border-top: 1px dashed silver;
    border-bottom: 1px dashed silver;
    padding: 1% 5%;
    line-height: 1.8;
}

#header_wrapper {
	padding: 36px 48px;
	display: block;
}

h1 {
	color: #557da1;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
	font-size: 30px;
	font-weight: 300;
	line-height: 150%;
	margin: 0;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
	text-shadow: 0 1px 0 #f5f5f5;
	-webkit-font-smoothing: antialiased;
}

h2 {
	color: #557da1;
	display: block;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
	font-size: 18px;
	font-weight: bold;
	line-height: 130%;
	margin: 16px 0 8px;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

h3 {
	color: #557da1;
	display: block;
	font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
	font-size: 16px;
	font-weight: bold;
	line-height: 130%;
	margin: 16px 0 8px;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

a {
	color: #557da1;
	font-weight: normal;
	text-decoration: underline;
}
<?php
