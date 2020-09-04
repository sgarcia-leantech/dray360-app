@inject('tenancy', 'tenancy')
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<style>
.inner-body-top-bar{
background-color: {{ $tenancy->getConfigurationValue('primary_color') }};
}
a {
color: {{ $tenancy->getConfigurationValue('primary_color') }} !important;
}
a.button{
color: #fff !important;
}
.button-primary {
background-color: {{ $tenancy->getConfigurationValue('primary_color') }} !important;
border-bottom: 8px solid {{ $tenancy->getConfigurationValue('primary_color') }} !important;
border-left: 18px solid {{ $tenancy->getConfigurationValue('primary_color') }} !important;
border-right: 18px solid {{ $tenancy->getConfigurationValue('primary_color') }} !important;
border-top: 8px solid {{ $tenancy->getConfigurationValue('primary_color') }} !important;
}
@media only screen and (max-width: 600px) {
.inner-body,.inner-body-top-bar {
width: 100% !important;
}

.footer {
width: 100% !important;
}
}

@media only screen and (max-width: 500px) {
.button {
width: 100% !important;
}
}
</style>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr><td class="pre-header"></td></tr>

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0">
<span class="inner-body-top-bar" align="center" cellpadding="0" cellspacing="0" width="570"></span>
<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
  <td>{{ $header ?? '' }}</td>
  <!-- Body content -->
<tr>
<td class="content-cell">
{{ Illuminate\Mail\Markdown::parse($slot) }}

{{ $subcopy ?? '' }}
</td>
</tr>
</table>
</td>
</tr>

{{ $footer ?? '' }}
</table>
</td>
</tr>
</table>
</body>
</html>
