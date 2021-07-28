<?php

namespace Pebble\CLI;

use Pebble\Auth;
use diversen\Cli\Utils;

class User
{

    // Return main commands help
    public function getCommand()
    {
        return
        array(
            'usage' => 'Command to alter auth table (users)',
            'options' => array(
                '--create-user' => 'Create a new user',
            ),

            
            // //
            // 'arguments' => array(
            //     'File' => 'Read from a file and out put to stdout',
            // ),
            
        );
    }


    public function runCommand($args)
    {
        $auth = new Auth();

        $utils = new Utils();
        if ($args->getFlag('create-user')) {
            $email = trim($utils->readSingleline("Enter email: "));
            $password = trim($utils->readSingleline("Enter password: "));

            if (!empty($email) && !empty($password)) {
                $auth->create($email, $password);
                $row = $auth->getByWhere(['email' => $email]);
                $res = $auth->verifyKey($row['random']);
                if ($res) {
                    $utils->echoStatus('Success', 'g', 'User has been created');
                    return 0;
                }
            }

            $utils->echoStatus('Error', 'r', 'Something went wrong. Try again');
            return 128;


        }
        
        return 0;
    }
}
