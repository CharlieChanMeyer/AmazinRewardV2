<?php
namespace Deployer;

require 'recipe/symfony.php';

// Config

set('repository', 'git@github.com:CharlieChanMeyer/AmazinRewardV2.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('ec2-15-168-13-179.ap-northeast-3.compute.amazonaws.com')
    ->set('remote_user', 'ec2-user')
    ->set('deploy_path', '/var/www/html/AmazonRewards')
    ->set('identity_file','~/Documents/web-server.pem');

// Hooks

after('deploy:failed', 'deploy:unlock');
