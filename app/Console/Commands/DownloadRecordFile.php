<?php
/**
 * Created by PhpStorm.
 * User: George
 * Date: 1/6/17
 * Time: 22:32
 */

namespace App\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait DownloadRecordFile
{
    public $directory;

    /**
     * 执行下载文件
     * @param $method
     * @param int $records
     * @param $attribute
     */
    public function download($method, $records = 0, $attribute)
    {
        $clinet = new Client();
        //判断并设置目录
        if ($method == 'in') {
            $this->directory = './records/callin/';
        } elseif ($method == 'out') {
            $this->directory = './records/callout/';
        }
        //输出信息
        $this->info('Start Dowanload file...');

        //创建异步请求
        $pool = new Pool($clinet, $this->sendRequest($records, $attribute), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) {
                $filename = substr($response->getHeader('Content-Disposition')[0], 21);
                if (Storage::disk('local')->exists($this->directory.substr($filename, 8, 8).'/'.$filename)) {
                    Log::info($filename.' already exists!');
                    $this->info($filename.' already exists!');
                } else {
                    try {
                        Storage::put($this->directory.substr($filename, 8, 8).'/'.$filename, $response->getBody()->getContents());
                        $this->info('Downloaded '.$filename);
                    } catch (\Exception $exception) {
                        Log::error($exception->getMessage());
                    }
                }
            },
            'rejected' => function ($reason, $index) {
                Log::error($reason);
            }
        ]);
        $promise = $pool->promise();
        $promise->wait();
    }

    public function sendRequest($data, $attribute)
    {
        foreach ($data as $item){
            if ($item['record_file']) {
                $url = 'http://api.clink.cn/'.substr($item['record_file'], 8, 8).'/'.$item['record_file'].'?hotline='.$attribute['hotline'].'&userName='.$attribute['userName'].'&pwd='.$attribute['pwd'].'&seed='.$attribute['seed'];
                yield new Request('GET', $url);
               $this->info('ID: '.$item['id']);
            }
        }
    }
}