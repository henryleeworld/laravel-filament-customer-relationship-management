# Laravel 10 Filament 客戶關係管理

Filament 客戶關係管理採用快速建立簡捷的 TALL（Tailwind CSS、Alpine.js、Laravel 和 Livewire）堆疊應用程式的工具組，能夠幫助你輕鬆快速地找到潛在客戶，並且將他們精準地歸類，進而幫助銷售與行銷人員將精力投入在正確的客戶身上，銷售人員能夠因此提升交易的成功機率，行銷人員也能更容易地將潛在客戶轉換為有效客戶。

## 使用方式
- 打開 php.ini 檔案，啟用 PHP 擴充模組 intl，並重啟服務器。
- 把整個專案複製一份到你的電腦裡，這裡指的「內容」不是只有檔案，而是指所有整個專案的歷史紀錄、分支、標籤等內容都會複製一份下來。
```sh
$ git clone
```
- 將 __.env.example__ 檔案重新命名成 __.env__，如果應用程式金鑰沒有被設定的話，你的使用者 sessions 和其他加密的資料都是不安全的！
- 當你的專案中已經有 composer.lock，可以直接執行指令以讓 Composer 安裝 composer.lock 中指定的套件及版本。
```sh
$ composer install
```
- 產生 Laravel 要使用的一組 32 字元長度的隨機字串 APP_KEY 並存在 .env 內。
```sh
$ php artisan key:generate
```
- 執行 __Artisan__ 指令的 __migrate__ 來執行所有未完成的遷移，並執行資料庫填充（如果要測試的話）。
```sh
$ php artisan migrate --seed
```
- 執行安裝 Vite 和 Laravel 擴充套件引用的依賴項目。
```sh
$ npm install
```
- 執行正式環境版本化資源管道並編譯。
```sh
$ npm run build
```
- 在瀏覽器中輸入已定義的路由 URL 來訪問，例如：http://127.0.0.1:8000。
- 可以經由 `/admin/login` 來進行登入，預設的電子郵件和密碼分別為 __admin@admin.com__ 和 __password__ 。

----

## 畫面截圖
![](https://i.imgur.com/CxpUnGA.png)
> 瞭解客戶的進展情況

![](https://i.imgur.com/eJ06DjP.png)
> 以免任務行程時間發生衝突

![](https://i.imgur.com/5ppuS6d.png)
> 可以從過去各階段所紀錄的線索，釐清客戶痛點與卡關之處，並以此為切入點，重新與顧客開啟對話，提高成交的命中率

![](https://i.imgur.com/4iK9kvE.png)
> 點擊客戶檢視詳細資訊

![](https://i.imgur.com/BReHG3T.png)
> 設定客戶生命週期階段

![](https://i.imgur.com/87hX0ag.png)
> 邀請對象按一下邀請電子郵件中的「建立帳戶」按鈕進入註冊
