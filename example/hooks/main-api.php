<?php
$repo_dir = '/var/www/hooks/mirrors/quest_rewards_api.git';
$web_root_dir = '/var/www/html/staging-api.questrewards.com';

// Full path to git binary is required if git is not in your PHP user's path. Otherwise just use 'git'.
$git_bin_path = '/usr/bin/git';
// Do a git checkout to the web root
exec('cd ' . $repo_dir . ' && ' . $git_bin_path . ' fetch');
exec('cd ' . $repo_dir . ' && GIT_WORK_TREE=' . $web_root_dir . ' ' . $git_bin_path . ' checkout -f');
// Log the deployment
$commit_hash = shell_exec('cd ' . $repo_dir . ' && ' . $git_bin_path . ' rev-parse --short HEAD');
file_put_contents('logs/staging-api-deployed.log', date('m/d/Y h:i:s a'), FILE_APPEND);

?>
