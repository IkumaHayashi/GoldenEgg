<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\WebDriverDimension;


class ScrapeFincanceInformation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goldenegg:ScrapingFinanceInformation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    // ChromeDriver
    private $driver;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Chromeを起動する
        $host = config('scraping.chrome_url');
        $options = new ChromeOptions();
        $options->addArguments([
            '--no-sandbox',
            '--headless',
            "--user-agent=Mozilla/5.0 (iPhone; CPU iPhone OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A403 Safari/8536.25",
        ]);
        $options->setExperimentalOption('w3c', false);

        $caps = DesiredCapabilities::chrome();
        $caps->setCapability(ChromeOptions::CAPABILITY, $options);

        $this->driver = RemoteWebDriver::create($host, $caps);
        $this->driver->manage()->window()->setSize(new WebDriverDimension(414, 736));
    }

    public function __destruct()
    {
        // close the browser
        $this->driver->close();

        parent::__destruct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 株式の基本情報を取得
        $codes = [
            "3407",
            "2169",
            "2393",
            "4326",
            "4327",
            "8058",
            "8096",
            "8316",
            "8593",
            "8898",
            "2914",
            "8316",
            "9436",
            "8750",
            "9432",
            "9986"
        ];
        // $this->getStockInformation($codes);

        foreach (\App\User::get() as $user) {
            $this->getExecutionHistories($user);
        }
        

    }

    private function getStockInformation(array $codes)
    {

        foreach ($codes as $code) {

            sleep(2);
            $this->driver->get("https://www.nikkei.com/nkd/company/gaiyo/?scode=${code}");
            $file = __DIR__ . "/debug.png";
            $this->driver->takeScreenshot($file);
            $companyName = $this->driver->findElement(WebDriverBy::xpath('//*[@id="CONTENTS_MAIN"]/div[3]/div/div/h1'))->getText();
            echo '企業名:' . $companyName . PHP_EOL;

            $kabuka = $this->driver->findElement(WebDriverBy::xpath('//*[@id="CONTENTS_MAIN"]/div[4]/dl[1]/dd'))->getText();
            $kabuka = str_replace(',','',$kabuka);
            $kabuka = str_replace('円','',$kabuka);
            $kabuka = str_replace(' ','',$kabuka);
            echo '株価:' . $kabuka . PHP_EOL;
            $market = $this->driver->findElement(WebDriverBy::xpath('//*[@id="JSID_select_ba"]/span'))->getText();
            echo '市場:' . $market . PHP_EOL;
            $sector = $this->driver->findElement(WebDriverBy::xpath('//*[@id="basicInformation"]/div/div[2]/div/div/table/tbody/tr[8]/td'))->getText();
            echo '業種:' . $sector . PHP_EOL;

            // $node = $crawler->filterXPath(');
            // $industry = $node->text();
            sleep(2);
            $this->driver->get("https://www.nikkei.com/nkd/company/kessan/?scode=${code}");

            // 各決算期の財務情報のエレメントを取得
            $element = $this->driver->findElements(WebDriverBy::cssSelector('.m-tableType01.a-mb12'))[0];
            
            //$eps = $this->driver->findElement(WebDriverBy::xpath('//*[@id="CONTENTS_MAIN"]/div[6]/div/div[2]/div[4]/div[2]/table/tbody[2]/tr[1]/td[5]'))->getText();
            // 最新一株利益（円）
            $eps = $element->findElement(WebDriverBy::xpath('div[2]/table/tbody[2]/tr[1]/td[5]'))->getText();
            echo 'EPS:' . $eps . PHP_EOL;
    
            // 最新一株配当（円）
            $dividend = $element->findElement(WebDriverBy::xpath('div[2]/table/tbody[2]/tr[2]/td[5]'))->getText();
            echo '配当:' . $dividend . PHP_EOL;

            $haitoRimawari = $dividend / $kabuka * 100;
            echo '配当利回り:' . $haitoRimawari . PHP_EOL;

            // DBへ格納
            $stock = \App\Models\Stock::where('code', $code)->first();
            if(is_null($stock)){
                $stock = new \App\Models\Stock();
                $stock->code = $code;
            }
            $stock->company_name = $companyName;
            $stock->market = $market;
            $stock->sector = $sector;
            $stock->eps = $eps;
            $stock->dividend = $dividend;
            $stock->save();
            
        }
    }

    private function getExecutionHistories(\App\User $user){
        //dd($user->security->loginid);
        //dd($user->security->password);
        $this->driver->get("https://trade.sbineomobile.co.jp/login");
        //ID入力
        $element = $this->driver->findElement(WebDriverBy::name("username"));
        $element->clear();
        $element->sendKeys($user->security->loginid);
        //パスワード入力
        $element = $this->driver->findElement(WebDriverBy::name("password"));
        $element->clear();
        $element->sendKeys($user->security->password);

        $element = $this->driver->findElement(WebDriverBy::id("neo-login-btn"));
        $element->getLocationOnScreenOnceScrolledIntoView(); // ボタンが表示されるまでスクロール
        $element->click();

        // 約定履歴へ移動
        $this->driver->get("https://trade.sbineomobile.co.jp/trade/domestic/tradeHistory/executionHistory");
        $this->driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath('//*[@id="neo-C5-main"]/section[3]/div[1]'))
            //WebDriverExpectedCondition::titleIs('SBIネオモバイル証券')   //ページタイトルが「My Page」になるまで最大３０秒待つ
        );

        // 表示期間のドロップダウンリストを取得
        // 表示期間を３ヶ月にする
        $element = $this->driver->findElement(WebDriverBy::xpath('//*[@id="neo-C5-main"]/section[3]/div[1]/select/option[4]'));
        $element->click();
        $this->driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('execution-histories-layout'))
        );
        sleep(3);
        $this->driver->takeScreenshot(__DIR__ . "/debug.png");
        // 一番下までスクロール
        $script = "window.scrollTo(0, document.body.scrollHeight);";
        $this->driver->executeScript($script);
        $this->driver->takeScreenshot(__DIR__ . "/debug1.png");
        $script = "window.scrollTo(0, document.body.scrollHeight);";
        $this->driver->executeScript($script);
        $this->driver->takeScreenshot(__DIR__ . "/debug2.png");
        $element = $this->driver->findElement(WebDriverBy::id('execution-histories-layout'));
        $elements = $element->findElements(WebDriverBy::cssSelector('.panel.buy'));
        foreach ($elements as $elem) {
            // 約定日を取得
            $yakujoDate = $elem->findElement(WebDriverBy::className('buy'))
                                    ->findElement(WebDriverBy::tagName('span'))
                                    ->getAttribute('innerHTML');
            $yakujoDate = str_replace('約定日：','',$yakujoDate);
            print_r("約定日:${yakujoDate}" . PHP_EOL);

            // 証券コードを取得
            $code = $elem->findElement(WebDriverBy::className('ticker'))
                            ->getAttribute('innerHTML');
            print_r("コード:${code}" . PHP_EOL);

            // 約定数量を取得
            $yakujoInfoElements = $elem->findElement(WebDriverBy::className('label'))
                                        ->findElements(WebDriverBy::tagName('li'));
            $yakujoAmount = $yakujoInfoElements[0]->getAttribute('innerHTML');
            $yakujoAmount = str_replace('約定数量：','',$yakujoAmount);
            $yakujoAmount = str_replace('株','',$yakujoAmount);
            $yakujoAmount = str_replace(' ','',$yakujoAmount);
            $yakujoAmount = str_replace(array("\r\n", "\r", "\n"), '', $yakujoAmount);
            print_r("約定数量:${yakujoAmount}" . PHP_EOL);
            
            // 約定単価を取得
            $yakujoUnitPrice = $yakujoInfoElements[1]->getAttribute('innerHTML');
            $yakujoUnitPrice = str_replace('約定単価：<span>','',$yakujoUnitPrice);
            $yakujoUnitPrice = str_replace('</span>','',$yakujoUnitPrice);
            $yakujoUnitPrice = str_replace(' ','',$yakujoUnitPrice);
            $yakujoUnitPrice = str_replace(',','',$yakujoUnitPrice);
            $yakujoUnitPrice = str_replace('円','',$yakujoUnitPrice);
            $yakujoUnitPrice = str_replace(array("\r\n", "\r", "\n"), '', $yakujoUnitPrice);
            print_r("約定単価:${yakujoUnitPrice}" . PHP_EOL);

            
            // DBへ格納
            $executionHistory = \App\Models\ExecutionHistory::where('code', $code)
                                                            ->where('execution_date',$yakujoDate)
                                                            ->first();
            if(isset($executionHistory)){
                continue;
            }
            $executionHistory = new \App\Models\ExecutionHistory();
            $executionHistory->execution_date = $yakujoDate;
            $executionHistory->code = $code;
            $executionHistory->quantity = $yakujoAmount;
            $executionHistory->unitprice = $yakujoUnitPrice;
            $executionHistory->user_id = $user->id;
            $executionHistory->save();
            
        }
        // $file = __DIR__ . "/debug.png";
        // $this->driver->takeScreenshot($file);
    }
}
