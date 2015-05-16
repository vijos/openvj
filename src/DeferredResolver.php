<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ;

abstract class DeferredResolver
{
    protected $chunkSize;

    protected $queryValues = [];
    protected $targetRefs = [];

    /**
     * @param int $chunkSize 分批请求大小
     */
    public function __construct($chunkSize = 30)
    {
        $this->chunkSize = $chunkSize;
    }

    /**
     * 发起查询请求
     *
     * @param $value
     * @return DeferredResolve
     */
    public function resolve($value)
    {
        $this->preprocessValue($value);
        $this->queryValues[] = $value;
        if (!isset($this->targetRefs[(string)$value])) {
            $this->targetRefs[(string)$value] = [];
        }
        return new DeferredResolve($this->targetRefs[(string)$value]);
    }

    /**
     * 执行请求并填充值
     */
    public function done()
    {
        $this->queryValues = array_values(array_unique($this->queryValues));
        $this->postprocessValues();

        $chunks = array_chunk($this->queryValues, $this->chunkSize);

        foreach ($chunks as $chunk) {
            $cursor = $this->query($chunk);
            foreach ($cursor as $document) {
                foreach ($this->targetRefs[$this->getValueField($document)] as &$target) {
                    $target = $document;
                    unset($target);
                }
            }
        }
    }

    /**
     * 在加入数组前对需要查询的值进行预处理，用于使数组适合进行 array_unique 等操作
     *
     * @param $value
     */
    protected function preprocessValue(&$value)
    {
    }

    /**
     * 在查询前对数组进行处理，用于使预处理过的数组满足查询所需的类型要求
     */
    protected function postprocessValues()
    {
    }

    /**
     * 查询一批值，返回游标
     *
     * @param $chunk
     * @return \MongoCursor
     */
    protected abstract function query($chunk);

    /**
     * 返回文档中用于查询的字段内容
     *
     * @param $document
     * @return mixed
     */
    protected abstract function getValueField($document);
}