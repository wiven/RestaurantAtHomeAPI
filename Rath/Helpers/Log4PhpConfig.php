<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 02-Oct-15
 * Time: 12:02 PM
 */

namespace Rath\Helpers;


class Log4PhpConfig
{
    //Errors possible with roling file appender. Fix: https://issues.apache.org/jira/browse/LOG4PHP-210


    public static function getConfig()
    {
        // LOCAL
        // APIDEV
        // TEST
        switch(APP_MODE){
            case 'LOCAL':
                return [
                    'appenders' => [
                        'fileAppender' => [
                            'class' => 'LoggerAppenderFile',
                            'layout' => [
                                'class' => 'LoggerLayoutPattern',
                                'params' => [
                                    'conversionPattern' => '%date{Y-m-d H:i:s,u} %-5level [%logger] %message%newline%ex'
                                ]
                            ],
                            'params' => [
                                'file' => 'myLog.log'
                            ]
                        ],
                        'echoAppender' => array(
                            'class' => 'LoggerAppenderEcho',
                            'layout' => array(
                                'class' => 'LoggerLayoutSimple',
                            ),
                            'params' => array(
                                'htmlLineBreaks' => 'true',
                            ),
                        ),
                    ],
                    'loggers' => [],
                    'renderers' => [],
                    'rootlogger' => [
                        'level' => 'DEBUG',
                        'appenders' => [
                            'fileAppender',
                            'echoAppender'
                        ]
                    ]
                ];
            default :
                return [
                    'rootlogger' => [
                        'appenders' => [
                            "default"
                        ]
                    ],
                    'appenders' => [
                        'default' => [
                            'class' => 'LoggerAppenderConsole',
                            'layout' => [
                                'class' => 'LoggerLayoutPattern',
                                'params' => [
                                    'conversionPattern' => '%date{Y-m-d H:i:s,u} %-5level [%logger] %message%newline%ex'
                                ]
                            ]
                        ]
                    ]
                ];

        }
    }
}