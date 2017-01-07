<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncRecord extends Command
{
    use DownloadRecordFile;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:record {method : in or out} {hotline : Your hotline} {username : Your username} {password : Your Password} {seed : Encryption parameters} {limit : The maximum value of no more than 5000} {startId=1} {--sql : Sync records to local database} {--file : Download record file to loacl disk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Call-in/Call-out Record';

    protected $client, $startId;

    /**
     * SyncRecord constructor.
     * @param Client $client
     */
    public function __construct(
        Client $client
    )
    {
        parent::__construct();
        $this->client = $client;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //设置起始ID
        $this->startId = $this->argument('startId');

        $attribute = [
            'hotline' => $this->argument('hotline'),
            'userName' => $this->argument('username'),
            'pwd' => md5(md5($this->argument('password')).$this->argument('seed')),
            'seed' => $this->argument('seed'),
            'limit' => $this->argument('limit')
        ];

        $response_attr = [
            'hotline' => $this->argument('hotline'),
            'userName' => $this->argument('username'),
            'pwd' => md5(md5($this->argument('password')).$this->argument('seed')),
            'seed' => $this->argument('seed'),
            'limit' => $this->argument('limit'),
            'id' => $this->startId
        ];

        switch ($this->argument('method')) {
            //判断要获取的记录是呼入还是呼出
            case 'in':
                //输出信息
                $this->info('Synchronizing Call-in Record...');
                //获取呼入记录综合
                $totalCount = $this->getTotalCount($this->argument('method'));
                //初始化进度条
                $bar = $this->output->createProgressBar(ceil($totalCount/$this->argument('limit')));

                //将任务分批执行
                for ($i = 1; $i <= ceil($totalCount/$this->argument('limit')); $i++) {
                    //初始化Curl请求
                    $response = $this->client->post('http://api.clink.cn/interfaceAction/cdrIbInterface!cdrIbDBCopy.action', [
                        'form_params' => $response_attr
                    ]);

                    //获取相应内容
                    $records = json_decode($response->getBody(), true)['msg']['data'];

                    //验证是否开启了同步数据库选项
                    if ($this->option('sql')) {
                        $data = collect($records);
                        //讲数据分块处理
                        $dataArray = $data->chunk(100);

                        //尝试写入数据库
                        try {
                            foreach ($dataArray as $item) {
                                DB::table('callin')->insert($item->toArray());
                            }
                        } catch (\Exception $exception) {
                            $this->error('Data already exists!');
                        }
                    }

                    //验证是否开启了下载文件选项
                    if ($this->option('file')) {
                        $this->download($this->argument('method'), $records, $attribute);
                    }

                    //将起始ID设为本次获取的数据末尾ID
                    $this->startId = last($records)['id'];
                    //进度条步进1
                    $bar->advance();
                }
                $bar->finish();
                break;
            case 'out':
                //输出信息
                $this->info('Synchronizing Call-out Record...');
                //获取呼入记录综合
                $totalCount = $this->getTotalCount($this->argument('method'));
                //初始化进度条
                $bar = $this->output->createProgressBar(ceil($totalCount/$this->argument('limit')));

                //将任务分批执行
                for ($i = 1; $i <= ceil($totalCount/$this->argument('limit')); $i++) {
                    //初始化Curl请求
                    $response = $this->client->post('http://api.clink.cn/interfaceAction/cdrObInterface!cdrObDBCopy.action', [
                        'form_params' => $response_attr
                    ]);

                    //获取相应内容
                    $records = json_decode($response->getBody(), true)['msg']['data'];

                    //验证是否开启了同步数据库选项
                    if ($this->option('sql')) {
                        $data = collect($records);
                        //将数据分块处理
                        $dataArray = $data->chunk(100);
                        
                        //尝试写入数据库
                        try {
                            foreach ($dataArray as $item) {
                                DB::table('callout')->insert($item->toArray());
                            }
                        } catch (\Exception $exception) {
                            $this->error('Data already exists!');
                        }
                    }

                    //验证是否开启了下载文件选项
                    if ($this->option('file')) {
                        $this->download($this->argument('method'), $records, $attribute);
                    }

                    //将起始ID设为本次获取的数据末尾ID
                    $this->startId = last($records)['id'];
                    //进度条步进1
                    $bar->advance();
                }
                $bar->finish();
                break;
            default:
                break;
        }
    }

    /**
     * Get Call Total Count
     * @return mixed
     * @throws \Exception
     */
    protected function getTotalCount($method)
    {
        $response_attr = [
            'hotline' => $this->argument('hotline'),
            'userName' => $this->argument('username'),
            'pwd' => md5(md5($this->argument('password')).$this->argument('seed')),
            'seed' => $this->argument('seed'),
            'limit' => $this->argument('limit'),
            'id' => 1
        ];

        //判断要获取的记录是呼入还是呼出
        if ($method == 'in') {
            $response = $this->client->post('http://api.clink.cn/interfaceAction/cdrIbInterface!cdrIbDBCopy.action', [
                'form_params' => $response_attr
            ]);

            //如果相应结果为error则抛出异常
            if(json_decode($response->getBody(), true)['result'] == 'error') {
                throw new \Exception('Please check the user information！');
            }

            //获取呼入总数据
            $totalCount = json_decode($response->getBody(), true)['msg']['totalCount'];
            //输出呼入总数据信息
            $this->info('Call-in Total: '.$totalCount);
            return $totalCount;
        } else {
            $response = $this->client->post('http://api.clink.cn/interfaceAction/cdrObInterface!cdrObDBCopy.action', [
                'form_params' => $response_attr
            ]);

            //如果相应结果为error则抛出异常
            if(json_decode($response->getBody(), true)['result'] == 'error') {
                throw new \Exception('Please check the user information！');
            }

            //获取呼入总数据
            $totalCount = json_decode($response->getBody(), true)['msg']['totalCount'];
            //输出呼出总数据信息
            $this->info('Call-in Total: '.$totalCount);
            return $totalCount;
        }
    }
}
