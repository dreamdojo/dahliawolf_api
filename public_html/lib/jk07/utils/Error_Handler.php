<?php
/**
 * User: JDorado
 * Date: 4/26/11
 */

class Error_Handler extends Jk_Base
{

    private static $log;
    private static $isregistered = false;

    protected static function initLog()
    {
        if( self::$log  == null)
        {
            self::$log = new Jk_Logger( APP_PATH . 'logs/php_error.txt', Jk_Logger::INFO);
        }
    }

    public static function registerShutdownHandler()
    {
        self::$isregistered = true;
        register_shutdown_function( array("Error_Handler", 'callShutdownHandler') );
    }

    public static function registerErrorHandler()
    {
        self::$isregistered = true;
        set_error_handler( array("Error_Handler", "callPhpError") );
    }

    protected static function trace($elevel = false, $errstr= false, $errfile = false, $errline=false)
    {
        if(self::$isregistered == false) return;
        self::initLog();

        $e          = error_get_last();
        $message    = ($elevel ? "$elevel :": "")  . ($errstr ? $errstr : $e['message']);

        $file = $errfile ? $errfile : str_replace(APP_PATH, '', $e['file']);
        $line = $errline ? $errline : $e['line'];
        
        self::$log->LogInfo( Jk_Base::getCallee(4) . "=> \t\t$message file: $file line: $line"  );

    }


    public static function callShutdownHandler()
    {
        if ( error_get_last() )
        {
            self::trace("ON_SHUTDOWN");
            self::$log->LogInfo( Jk_Base::getDebugStack() );
        }
    }

    public static  function user($m, $el = E_USER_NOTICE)
    {
        if( is_object($m) || is_array($m))
        {
            self::callPhpError($el, var_export($m, true));
            return;
        }

        self::callPhpError($el, var_export($m, true));
    }


    public static function callPhpError($errno, $errstr, $errfile = null, $errline=null)
    {
        if($errno == 0) return;

        if(!defined('E_STRICT'))            define('E_STRICT', 2048);
        if(!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 4096);

        $elevel = "Unknown error ($errno)";

        switch($errno)
        {
            case E_ERROR:               $elevel = "Error";                  break;
            case E_WARNING:             $elevel = "Warning";                break;
            case E_PARSE:               $elevel = "Parse Error";            break;
            case E_NOTICE:              $elevel = "Notice";                 break;
            case E_CORE_ERROR:          $elevel = "Core Error";             break;
            case E_CORE_WARNING:        $elevel = "Core Warning";           break;
            case E_COMPILE_ERROR:       $elevel = "Compile Error";          break;
            case E_COMPILE_WARNING:     $elevel = "Compile Warning";        break;
            case E_USER_ERROR:          $elevel = "User Error";             break;
            case E_USER_WARNING:        $elevel = "User Warning";           break;
            case E_USER_NOTICE:         $elevel = "User Notice";            break;
            case E_STRICT:              $elevel = "Strict Notice";          break;
            case E_RECOVERABLE_ERROR:   $elevel = "Recoverable Error";      break;
            default:                    $elevel = "Unknown error ($errno)"; break;
        }

        self::trace($elevel, $errstr, $errfile, $errline);

    }


}

?>