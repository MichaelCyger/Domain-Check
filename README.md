# Domain Check
This script monitors your domain portfolio, notes any changes, and notifies you by email.

## Introduction
Domain names are among the most valuable digital assets for companies and individuals. They represent your online presence, brand and business, they connect you to friends and customers through email and wallets, and they are often directly tied to revenue through ecommerce applications.

Yet many popular domain registrars lack a transactional history log for changes made to these assets over time. The absence of activity history leaves domain owners unaware of changes and more vulnerable to potential loss or theft of their valuable domains.

Manually inspecting my 100+ domain portfolio is time-consuming and tedious. Logging into my account and visually comparing my domain names to my offline list takes 5 minutes per day. I need an automated solution that emails me a summary, highlighting any changes so I can quickly identify issues that require further investigation.

In response to this unmet need, I have developed a script called "Domain Check" that fills the gap left by registrars. This script monitors your domain portfolio and notes any changes, initially focusing on GoDaddy but with the goal of expanding to multiple registrars in the future.

The sooner you notice an unanticipated discrepancy and notify your registrar, the sooner they can lock the domain and investigate fully. If you wait too long, a domain name may transfer to a registrar outside of your country jurisdiction and you may face a loss.

With the "Domain Check" script, you can easily monitor and verify domain name additions and removals in your GoDaddy account. You can also choose to receive email notifications as frequently as you desire. This feature ensures that you stay informed about additions (registrations and transfers-in) and removals (sales, drops and expirations), giving you peace of mind that no unauthorized changes occur without your knowledge.

Additionally, our script offers a change in count feature, allowing you to efficiently compare and track changes in your domain portfolio. This feature enables you to quickly identify any discrepancies or unexpected alterations.

In the email you receive, a full list of domain names in your portfolio is attached for archive and historical documentation, if needed.

<img src="https://github.com/MichaelCyger/Domain-Check/assets/121400468/f2f97b96-d69c-441d-aed3-f720cca7ec70" alt="Daily Domain Check" style="width: 400px;">

## Plea to Registrars

Every domain name registrar should prioritize transparency for domain owners and provide them with the necessary tools to monitor their domain portfolio, much like every bank provides a ledger of activity for every customer account.

[Gandi offers this functionality](https://docs.gandi.net/en/domain_names/common_operations/activity.html), and now-defunct Uniregstry provided it, but no other major registrars provides it -- let me know if you believe otherwise.

I'm not trying to single out any registrar for not providing a transactional log, but I would like to lift-up and promote those who do.

## Note About Code

I am not a full-time programmer. There will be code that a professional programmer will scoff at or deem "not optimized."

Heck, I'm not even sure how to use Github properly. If you fork this repository and then try to merge it back into my original, I'll have to ask you for help doing it. But I'm doing and willing to learn... go easy on me. :)

If you have any suggestions, I welcome them and you can reach me at michael at webxmedia dot com.

This script was written to operate on a WordPress website, because that's what I use. It uses PHP with no database; instead, it saves data to a text file. I believe it will run on all versions of PHP, but I have only tried it on 7.4.9. You will need access to CURL, which should be standard in your WordPress install. You'll also need WP Mail, which is hopefully set up by your hosting company.

## How to Implement

1. Sign into your GoDaddy account, visit https://developer.godaddy.com/keys, and click the "Create New API Key" button. Name your application "Domain Check API Script" (or similar), and select "Production" for the environment.

<img src="https://github.com/MichaelCyger/Domain-Check/assets/121400468/85a4317f-95f5-434a-9659-efcd123ea4d4" alt="Create New API Key at GoDaddy" style="width: 400px;">

3. Using a file manager, on your website create a directory called "check" in the root directory (alternatively, you can create it in /wp-content/uploads/ so that it will automatically be backed-up, if you have a backup service).

4. Update the config.php file with your website details:

```
// Access setting
define('ACCESS_KEY', 'XXXXXXXXXXX'); // <== This is a long series of letters allowing access to the webpage

// GoDaddy API credentials
define('API_KEY', 'XXXXXXXXXXX'); // <== The "Key" from step #1 above
define('API_SECRET', 'XXXXXXXXXXX'); // <== The "Secret" from step #1 above
define('SHOPPER_ID', 'XXXXXXXXXXX'); // <== This is your GoDaddy customer # (aka shopper ID), upper right-hand corner of account

// Email settings
define('RECIPIENT_NAME', 'Your Name');
define('RECIPIENT_EMAIL', 'you@yourdomain.com');
define('SENDER_EMAIL', 'you@yourdomain.com');
define('CODE_LOCATION', 'https://yourdomain/path/to/script/'); // <== So if you need to make changes in the future, you remember where it is
```

<img src="https://github.com/MichaelCyger/Domain-Check/assets/121400468/22208fc9-c1b6-4d97-a103-10b3994b8120" alt="GoDaddy Shopper ID or Customer ID" style="width: 400px;">

5. Copy the files domains.php and config.php into that directory.

6. Setup a cron job to run the domains.php script as often as you wish.

I run it once a day at 4am so an email is waiting for me in my inbox when I wake up in the morning.

There are many plugins (like https://wordpress.org/plugins/wp-crontrol/) that will allow you to run an automated task using cron, a scheduler tool that WordPress uses. A search for "wordpress cron plugin" will reveal options.

However, I've chosen https://crontap.com/, which has a much easier user interface. Crontap also offers a free version that should allow you to meet the needs of this script. (I pay for an unlimited "Pro" account because I use it in other applications.)

## Miscellaneous

* As you are setting up the script, the on-page confirmations will let you know if there's an error or if it ran properly. Once you get it running properly, you can set up a cron job and then just look for the email to arrive daily.

* If you locate the folder in the root, then "../wp-load.php" will work in the domains.php file. But if you locate it in your /wp-content/uploads/ folder then you will have to modify all instances of "../wp-load.php" to "../../../wp-load.php".

* This script currently only operates at GoDaddy, my preferred registrar. You can research your registrar's API documentation (https://developer.godaddy.com/doc/endpoint/domains#/v1/list) and modify this script accordingly.

* I noticed that if you put more than "1000" in this domains.php line it will produce an error:
```
$apiEndpoint = 'https://api.godaddy.com/v1/domains?statuses=ACTIVE&statusGroups=VISIBLE&limit=1000&includes=';
```

Sorry for those with large portfolios :(

## Frequently Asked Questions

Q: My script won't run. What's going on?

A: In your config.php if you set your ACCESS_KEY to "kJh7Ytf3hGGt67Uy5R3wE3np0kou783" (for example), then you will access the script at https://yourdomain.com/check/domains.php?pwd=kJh7Ytf3hGGt67Uy5R3wE3np0kou783. Don't use characters other than numbers and letters for ease of use.

Q: I was notified that a domain was removed from my account at GoDaddy, what should I do?

A: Sign into your GoDaddy account, visit https://dcc.godaddy.com and look for your domain to determine if it entered "expired" or another state that is not "active" -- which is what this script looks for. If the domain was listed for sale on Dan.com or Afternic.com, then check if the domain is "Pending Sale" at Afternic. Should those options not answer the question, then contact your Premier Services account manager, or -- if you don't have one -- contact GoDaddy customer support.

Q: How is security handled with this code?

A: The config.php should not be accessible via the web if your website security is set up properly. The domains.php will not run unless it includes a querystring with a key you provided in the config.php. Yes, the long key can be hacked if you know the script location but the worst that can happen is the code will be run and an email will be sent to you. If you prefer, comment out on-page echo statements in the code for extra security.

Q: Why not track my domains with [Watch My Domains](https://domainpunch.com/wmdpro/) or another program like that?

A: Watch My Domains is a great program. But in order to track your domains, you need to input your domains and keep them updated. I wrote this script so that I don't have to update a third-party system, because inevitably I'll forget to do that.

Q: I have a question not asked here. How can I reach you?

A: [DM me on Twitter](https://twitter.com/messages/84946341-84946341), or email me at michael at webxmedia dot com.
