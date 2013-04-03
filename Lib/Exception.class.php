<?php

namespace meSmart;

class Exception extends \Exception {

    /**
     * 异常类型
     *
     * var string
     */
    private $type;

    /**
     * 是否存在多余调试信息
     *
     * var bealoon
     */
    private $extra;

    /**
     * 架构函数
     *
     * @param string $message 异常信息
     *
     * @return void
     */
    public function __construct($message, $code = 0, $extra = false)
    {
        parent::__construct($message, $code);
        $this->type = get_class($this);
        $this->extra = $extra;
    }

    /**
     * 异常输出 所有异常处理类均通过__toString方法输出错误
     * 每次异常都会写入系统日志
     * 该方法可以被子类重载
     *
     * @return array
     */
    public function __toString()
    {
        $trace = $this->getTrace();

        // 通过throw_exception抛出的异常要去掉多余的调试信息
        if($this->extra) {
            array_shift($trace);
        }

        $this->class    = isset($trace[0]['class']) ? $trace[0]['class'] : '';
        $this->function = isset($trace[0]['function']) ? $trace[0]['function'] : '';
        $this->file     = $trace[0]['file'];
        $this->line     = $trace[0]['line'];
        $file           = file($this->file);
        $traceInfo      = '';
        $time           = date('y-m-d H:i:m');

        foreach($trace as $t) {
            $traceInfo .= '['.$time.'] '.$t['file'].' ('.$t['line'].') ';
            $traceInfo .= $t['class'].$t['type'].$t['function'].'(';
            $traceInfo .= implode(', ', $t['args']);
            $traceInfo .= ")\n";
        }
        $error['message']   = $this->message;
        $error['type']      = $this->type;
        $error['detail']    = L('_MODULE_').'['.CONTROLLER_NAME.'] '.L('_ACTION_').'['.ACTION_NAME.']'."\n";
        $error['detail']   .= ($this->line-2).': '.$file[$this->line-3];
        $error['detail']   .= ($this->line-1).': '.$file[$this->line-2];
        $error['detail']   .= '<font color="#FF6600" >'.($this->line).': <strong>'.$file[$this->line-1].'</strong></font>';
        $error['detail']   .= ($this->line+1).': '.$file[$this->line];
        $error['detail']   .= ($this->line+2).': '.$file[$this->line+1];
        $error['class']     = $this->class;
        $error['function']  = $this->function;
        $error['file']      = $this->file;
        $error['line']      = $this->line;
        $error['trace']     = $traceInfo;

        // 记录 Exception 日志
        if(C('LOG_EXCEPTION_RECORD')) {
            \Log::Write('('.$this->type.') '.$this->message);
        }

        // 输出
        return $error;
    }
}