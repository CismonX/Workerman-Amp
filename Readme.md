## Workerman-Amp

### 概述

[Amp](http://amphp.org/)是一个事件驱动的PHP框架，与`ReactPHP`类似。

本项目用于将`Amp`的event-loop应用于`Workerman`，从而可以在`Workerman`中使用基于`Amp`的高性能组件，例如异步`MySQL`，异步`Redis`，异步HTTP客户端等。

### 使用说明

1. 将`src`目录下的`Amp.php`复制到`Workerman\Events`下。

2. 将目录`Amp`及其包含的所有文件复制到项目目录中可以被自动加载的位置下（PSR-4规范）。

3. 将`Amp`设置为`Workerman`所使用的event-loop。如下：

```php
Worker::$eventLoopClass = '\\Workerman\\Events\\Amp';
```

### 注意

1. 本项目中使用的`Amp`是笔者略加修改的版本。由于`Amp`中的回调函数的首个参数是`$watcher_id`而不是`$fd`，所以不可以直接将`Workerman`中的事件回调作为Watcher。因此笔者修改了部分Watcher回调的传参顺序，使其与`Workerman`兼容。这样做的后果是不能保证其他`Amp`组件的可用性。在使用这些组件前需要检查其源码并根据情况作出相应修改。

2. 笔者还移除了`Amp\Promise`对`React\Promise\PromiseInterface`的支持。如果有需要，开发者可以自行添加回来。以上所有对Amp源码的修改，可以与[官方仓库的master分支](https://github.com/amphp/amp)作对比。

3. 笔者仅对本项目进行过少量测试，不能保证其稳定性。请避免将其应用于生产环境。