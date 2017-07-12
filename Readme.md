## Workerman-Amp

### 概述

[Amp](http://amphp.org/)是一个事件驱动的PHP框架，与`ReactPHP`类似。

本项目用于将`Amp`的event-loop应用于`Workerman`，从而可以在`Workerman`中使用基于`Amp`的高性能组件，例如异步`MySQL`，异步`Redis`，异步HTTP客户端等。

笔者修改了本项目的源码，使其兼容原生`Amp`。现在无需修改`Amp`的源码就可以在项目中使用其所有组件。以后笔者可能会发布一些Demo。

### 使用说明

1. 将`src`目录下的`Amp.php`复制到`Workerman\Events`下，或者你可以选择使用其他方式加载。

2. 将`Amp`加载到当前项目中。（建议使用composer管理依赖）

3. 将`Amp`设置为`Workerman`所使用的event-loop。如下：

```php
Worker::$eventLoopClass = '\\Workerman\\Events\\Amp';
```