<?php
namespace Di;

class Instance
{
    // 仅有的属性，用于保存类名、接口名或者别名
    public $id;

    // 构造函数，仅将传入的ID赋值给 $id 属性
    protected function __construct($id)
    {
        $this->id = $id;
    }

    // 静态方法创建一个Instance实例
    public static function of($id)
    {
        return new static($id);
    }

    // 静态方法，用于将引用解析成实际的对象，并确保这个对象的类型
    public static function ensure($reference, $type = null, $container = null)
    {
    }

    // 获取这个实例所引用的实际对象，事实上它调用的是
    public function get($container = null)
    {
    }
}
