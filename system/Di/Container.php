<?php
namespace Di;

use Exception\InvalidConfigException;

// 依赖注入容器
class Container implements ArrayAccess
{
    // 保存已生成的单例
    private $singletons = [];
    // 保存已定义的依赖
    private $definitions = [];
    // 保存传入的参数
    private $params = [];
    // 缓存ReflectionClass对象
    private $reflections = [];
    // 缓存依赖信息
    private $dependencies = [];

    // 注册依赖
    public function set($class, $definition = [], $params = [])
    {
        $this['definitions'][$class] = $this->normalizeDefinition($class, $definition);
        $this['params'][$class] = $params;
        // 使用set申明依赖，说明这个不需要单例
        unset($this['singletons'][$class]);
        return $this;
    }

    // 注册单例依赖
    public function setSingleton($class, $definition = [], $params = [])
    {
        $this['definitions'][$class] = $this->normalizeDefinition($class, $definition);
        $this['params'][$class] = $params;
        // 初始化单例为null
        $this['singletons'][$class] = null;
        return $this;
    }

    // 获取实例
    public function get($class, $params = [], $config = [])
    {
        // 先看是否有实例化过的单例有的话直接使用了 - note: isset(null) 返回false
        if (isset($this['singletons'][$class])) {
            return $this['singletons'][$class];
        }
        // 没有定义过依赖 - 说明不依赖其他类 或者 不需要[显式]定义依赖(构造函数包含了依赖信息)
        if (! isset($this['definitions'][$class])) {
            return $this->build($class, $params, $config);
        }
        // 定义过依赖的话 那就开始解决依赖吧
        $definition = $this['definitions'][$class];
        if (is_callable($definition)) {
            // 合并参数
            // 这里跟yii不同 貌似定义了 callable 的参数之后 肯定不会存在$this->params[$class]的
            // 所以这里省略了 mergeParams 和 resolveDependencies 这2步 就是下面注释的这一句
            // $params = $this->resolveDependencies($this->mergeParams($class, $params));

            // 这里也是一个约定吧 第一个参数为 di容器 第二个为 参数 第三个为 配置
            // 可以用来实例化需要配置的类
            $object = call_user_func($definition, $this, $params, $config);
        } elseif (is_array($definition)) {
            // 这里肯定有键值为class的元素 因为在set的时候已经保证过了
            $dependClass = $definition['class'];
            unset($definition['class']);
            // 合并参数
            $params = $this->mergeParams($class, $params);
            // 合并依赖定义时的配置与获取实例时传入的配置
            $config = array_merge($definition, $config);
            // 这里应该是区别是[别名定义]还是[类名定义的]
            // 别名定义的话 会走else
            // 类名定义的话 走if - 具体参考 normalizeDefinition 的逻辑
            if ($class === $dependClass) {
                $object = $this->build($class, $params, $config);
            } else {
                $object - $this->get($class, $params, $config);
            }

        } elseif (is_object($definition)) {
            //是对象的话代表全局只用这一个 - 故保存为单例
            return $this['singletons'][$class] = $definition;
        } else {
            // 最后什么都不满足就抛出错误吧
            throw new InvalidConfigException('依赖定义的参数类型有误: ' . gettype($definition));
        }
        // singletons 中含有这个键值 说明这个类需要单例
        if (array_key_exists($class, $this['singletons'])) {
            $this['singletons'][$class] = $object;
        }
        return $object;
    }

    // 解析依赖 - 传入一个类名 获取构造函数中依赖的类
    protected function getDependencies($class)
    {
        // 这两个总是会同时存在的 所以只判断一个就好了
        if (isset($this['reflections'][$class])) {
            return [$this['reflections'][$class], $this['dependencies'][$class]];
        }
        $dependencies = [];
        // 通过反射api实例化一个对象
        $reflection = new reflectionClass($class);
        // 获取构造函数
        $constructor = $reflection->getConstructor();
        // 不是null 说明存在构造函数
        if ($constructor !== null) {
            // 遍历构造函数里的参数
            foreach ($constructor->getParameters() as $param) {
                // 如果有默认值 说明是简单类型 将默认值作为依赖
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                } else {
                    //否则获取 该参数的类型提示类(类型提示为类名：得到一个反射类 或者 null)
                    $c = $param->getClass();
                    // 拿到类型提示的类名(如果有)
                    $name = $c === null ? null : $c->getName();
                    // 获取一个该类的Instance示例
                    $dependencies[] = Instance::of($name);
                }
            }
        }
        // 缓存ReflectionClass对象 以提高效率
        $this['reflections'][$class] = $reflection;
        // 缓存依赖信息
        $this['dependencies'][$class] = $dependencies;
        return [$reflection, $dependencies];
    }

    // 解决依赖
    protected function resolveDependencies($dependencies, $reflection = null)
    {
        // 遍历依赖参数 获取依赖 并且可以检查是否有必填参数未赋值
        foreach ($dependencies as $index => $dependency) {
            if ($dependency instanceof Instance) {
                if ($dependency->id !== null) {
                    $dependencies[$index] = $this->get($dependency->id);
                } elseif ($reflection !== null) {
                    $class = $reflection->getName();
                    $name = $reflection->getConstructor()[$index]->getName();
                    throw new InvalidConfigException("实例化{$class}时，缺少了必填参数{$name}");
                }
            }
        }
        return $dependencies;
    }

    // 创建实例
    protected function build($class, $params = [], $config = [])
    {
        list($reflection, $dependencies) = $this->getDependencies($class);
        // $params 的内容补充 覆盖到依赖信息中
        // 构造函数需要的[必填参数]可以从这里传入 - 传入之后 会覆盖 Instance->id 为null的参数
        // 为null代表这是一个没有默认值的参数(必填)如果这里没有覆盖，即必须赋值的参数没有赋值，会在 resolveDependencies 的时候报错)
        $dependencies = array_merge($dependencies, $params);

        // 构造函数需要参数 并且config不为空 那么最后一个参数就是配置参数(约定)
        if (!empty($dependencies) && ! empty($config)) {
            $dependencies[count($dependencies) - 1] = $config;
        }
        // 解决依赖
        $dependencies = $this->resolveDependencies($dependencies, $reflection);
        // 通过反射api创建实例
        return $reflection->newInstanceArgs($dependencies);
    }

    // 合并参数
    protected function mergeParams($class, $params)
    {
        $_params = [];
        if (isset($this['params'][$class])) {
            $_params = $this['params'][$class];
        }
        // 用get时传入的参数覆盖之前定义的参数
        return $params + $_params;
    }

    // 标准化依赖定义数组
    protected function normalizeDefinition($class, $definition = [])
    {
        if (empty($definition)) {
            return ['class' => $class];
        } elseif (is_string($definition)) {
            return ['class' => $definition];
        } elseif (is_callable($definition) || is_object($definition)) {
            return $definition;
        } elseif (is_array($definition)) {
            if (! isset($definition['class'])) {
                throw new InvalidConfigException('依赖定义数组中必须包含键名为 “class” 的元素');
            }
            return $definition;
        } else {
            throw new InvalidConfigException('依赖定义格式有误');
        }
    }
}
