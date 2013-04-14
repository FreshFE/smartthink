#SmartThink PHP

> Firstly, Thanks for ThinkPHP, Laravel, Smarty & Composer.
> Secondly, ...

##Overview

当前版本还处在开发中，目前先使用v1.0.0_preview1进行调试。
常用接口变动不会太大，一些非常用类将进一步重构。

---

SmartThink PHP 是在ThinkPHP3.1.2的基础上开发而来。
采取了PHP5.3的最新特性而重新设计的框架，与正在开发中的ThinkPHP4一样对原有的TP进行了颠覆式的改变。

1. 加入PHP命名空间（namespace）机制，按照命名控制自动加载类；
2. 将原有的大量函数重构为静态类；
3. 重新设计了View类和模板引擎的机制，删除了原有的所有View类特性，全面使用Smarty；
4. 删除原有Router的解析方式，改为单一的pathinfo解析方法；
5. 彻底使用项目独立分组方式，优化相关常量的命名；
6. 新建Import, Redirect, Request, Response类；
7. 重构Controller类；
8. 彻底删除原有的扩展加载方式；
9. 全面对每个类，方法添加大量注释。

##Change log

##Installation & Setup