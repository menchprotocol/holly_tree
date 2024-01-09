
MENCH
Platform Routes
Copy/Paste the following code in routes.php
<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['translate_uri_dashes'] = FALSE;
$route['default_controller'] = "app/index"; //Redirects to default app
$route['404_override'] = 'app/load'; //Page not found

$route['InteractionType'] = "app/load/4593";
$route['reminder'] = "app/load/42216";
$route['routes'] = "app/load/42006";
$route['ServerTime'] = "app/load/41286";
$route['contacts'] = "app/load/40947";
$route['TreeLister'] = "app/load/40355";
$route['signapp'] = "app/load/32603";
$route['EmailImporter'] = "app/load/35983";
$route['upload'] = "app/load/13572";
$route['stats'] = "app/load/33292";
$route['tree'] = "app/load/13900";
$route['apps'] = "app/load/6287";
$route['RegularExpressionRemove'] = "app/load/32103";
$route['templates'] = "app/load/31077";
$route['checkin'] = "app/load/31076";
$route['OncePerPerson'] = "app/load/31072";
$route['terms'] = "app/load/14373";
$route['mytickets'] = "app/load/26560";
$route['RegularExpressionMatch'] = "app/load/26611";
$route['responses'] = "app/load/13980";
$route['event'] = "app/load/30838";
$route['SelectionTimeout'] = "app/load/28199";
$route['SnoozingSubscriber'] = "app/load/28917";
$route['Notifications'] = "app/load/28904";
$route['MultiApply'] = "app/load/27196";
$route['treeClick'] = "app/load/27970";
$route['ReceivedSMS'] = "app/load/27901";
$route['LoginAs'] = "app/load/27238";
$route['paypalapp'] = "app/load/27004";
$route['paypalpayment'] = "app/load/26595";
$route['messenger'] = "app/load/26582";
$route['guest'] = "app/load/14938";
$route['cache'] = "app/load/14599";
$route['CleanupDeletedIdeas'] = "app/load/14573";
$route['home'] = "app/load/14565";
$route['SocialLoginCallback'] = "app/load/14564";
$route['notFound'] = "app/load/14563";
$route['ledger'] = "app/load/4341";
$route['SocialLogin'] = "app/load/14436";
$route['communities'] = "app/load/13207";
$route['setupaccount'] = "app/load/14517";
$route['logout'] = "app/load/7291";
$route['login'] = "app/load/4269";
$route['SourceImporter'] = "app/load/13881";
$route['sheet'] = "app/load/13790";
$route['tokens'] = "app/load/13602";
$route['InteractionID'] = "app/load/4367";
$route['cron'] = "app/load/7274";
$route['gephiSync'] = "app/load/7278";
$route['SyncSearchIndex'] = "app/load/7279";
$route['reports'] = "app/load/12114";
$route['weights'] = "app/load/12569";
$route['or'] = "app/load/7712";
$route['SourceRandomAvatars'] = "app/load/12738";
$route['SourceIdeasSyncFix'] = "app/load/12736";
$route['discoveryInfo'] = "app/load/12733";
$route['SourceIdeaSyncPrivacy'] = "app/load/12732";
$route['IdeaInvalidTitles'] = "app/load/12731";
$route['SourceSearchReplace'] = "app/load/12730";
$route['InteractionMetadataView'] = "app/load/12722";
$route['memory'] = "app/load/4527";
$route['MySessionVariables'] = "app/load/12710";
$route['phpInfo'] = "app/load/12709";
$route['IdeaSearchReplace'] = "app/load/7259";
$route['IdeaOrphaned'] = "app/load/7260";
$route['IdeaDuplicates'] = "app/load/7261";
$route['icons'] = "app/load/7267";
$route['SourceDuplicates'] = "app/load/7268";
$route['SourceOrphaned'] = "app/load/7269";


$route['@([a-zA-Z0-9]+)'] = "e/e_layout/$1"; //Source
$route['~([a-zA-Z0-9]+)'] = "i/i_layout/$1"; //Ideate
$route['([a-zA-Z0-9]+)/([a-zA-Z0-9]+)'] = "x/x_layout/$1/$2"; //Discovery Sequence
$route['([a-zA-Z0-9]+)'] = "x/x_layout/0/$1/0"; //Discovery Single

