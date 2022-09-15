<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                /*font-family: 'Nunito', sans-serif;*/
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            nav {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 80px;
                z-index: 1;
            }

            nav ul {
                float: right;
                padding: 25px;
                display: inline-block;
                list-style-type: none;
                background-color: #fff;
                border-top-left-radius: 5px;
                border-bottom-left-radius: 5px;
                -webkit-box-shadow: 0px 0px 5px 0px rgba(170,170,170,1);
                -moz-box-shadow: 0px 0px 5px 0px rgba(170,170,170,1);
                box-shadow: 0px 0px 5px 0px rgba(170,170,170,1);
            }

            nav ul li {
                float: left;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-transform: uppercase;
            }

            nav ul li a {
                color: #636b6f;
                text-decoration: none;
            }

            main {
                z-index: 0;
                min-height: 90vh;
                position: relative;
            }

            .green-background {
                background-color: rgba(0, 203, 169, 0.71);
            }

            .underline-left {
                position: relative;
            }

            .underline-left::after {
                content: '';
                display: block;
                position: absolute;
                bottom: -10px;
                height: 5px;
                width: 25vw;
                background-color: #f0f0f0;
            }

            .underline-right {
                position: relative;
            }

            .underline-right::after {
                content: '';
                display: block;
                position: absolute;
                bottom: -10px;
                right: 0;
                height: 5px;
                width: 25vw;
                background-color: #f0f0f0;
            }

            #landing {
                padding-top: 15vh;
                padding-bottom: 9vh;
            }

            #landing div {
                height: 45vh;
                background-image: url('/big-logo.png');
                background-size: contain;
                background-position: center;
                background-repeat: no-repeat;
            }

            .wave-container {
                position: relative;
            }

            @keyframes animateWave {
                0% {
                    transform: scale(1, .75);
                }
                100% {
                    transform: scale(1, 1);
                }
            }

            .wave {
                display: inline-block;
                position: relative;
                width: 100%;
                vertical-align: middle;
                overflow: hidden;
                top: 0;
                left: 0;
            }

            .wave > svg {
                display: block;
                transform-origin: top;
                animation: animateWave 1000ms cubic-bezier(0.23, 1, 0.32, 1) forwards;
            }

            #content-1 {
                padding: 200px 50px 50px;
                position: relative;
                overflow: hidden;
            }

            #content-1 h1 {
                font-size: 36px;
                margin-top: 0;
            }

            @media screen and (min-width: 800px) {
                #content-1 .half-width {
                    width: 43%;
                }

                #content-1 .half-width:first-of-type::after {
                    content: '';
                    width: 50px;
                    height: calc(440px - 18vw);
                    position: absolute;
                    top: calc(190px + 1vw);
                    left: calc(50% - 30px);
                    right: calc(50% - 70px);
                    background: linear-gradient(to top left, #fff calc(50% - 1px), #aaa, #fff calc(50% + 1px) )
                }
            }

            @media screen and (max-width: 800px) {
                #content-1 .half-width {
                    width: 100%;
                    position: relative;
                }

                #content-1 .half-width:first-of-type {
                    margin-bottom: 50px;
                    margin-top: -50px;
                }

                #content-1 .half-width:first-of-type::after {
                    content: '';
                    width: calc(420px + 20vw);
                    height: 50px;
                    display: inline-block;
                    position: relative;
                    margin-top: 40px;
                    margin-left: calc(50% - 210px - 10vw);
                    margin-right: calc(50% - 210px - 10vw);
                    background: linear-gradient(to top left, #fff calc(50% - 1px), #aaa, #fff calc(50% + 1px) )
                }
            }

            #content-2 {
                padding: 50px;
            }

            #content-2-wave svg {
                transform-origin: bottom;
            }

            #content-2 article {
                overflow: hidden;
            }

            #content-2 article h1 {
                color: #f0f0f0;
                font-size: 30px;
                text-transform: capitalize;
                margin-bottom: 20px;
            }

            #content-2 article p {
                color: #f0f0f0;
                font-size: 24px;
                font-weight: bold;
                margin-top: 20px;
            }

            #content-2 article div {
                width: 60%;
                padding: 20px;
                display: block;
                margin-top: 30px;
                border-radius: 10px;
                background-color: #eee;
            }

            @media screen and (min-width: 1000px) {
                #content-2 {
                    padding: 20px;
                }

                #content-2 article {
                    width: 60%;
                }

                #content-2 article div {
                    width: calc(90% - 40px);
                }
            }

            @media screen and (max-width: 1000px) {
                #content-2 article div {
                    width: 80%;
                }
            }

            @media screen and (max-width: 800px) {
                #content-2 article div {
                    width: 90%;
                }
            }
        </style>
    </head>
    <body>
        <nav>
            <ul>
                @auth
                    <li><a href="{{ route('stocks.all') }}">Stocks</a></li>
                    <li><a href="{{ route('profile.index') }}">Profile</a></li>
                @else
                    <li><a href="{{ route('login') }}">Login</a></li>
                    <li><a href="{{ route('register') }}">Register</a></li>
                @endauth
            </ul>
        </nav>
        <main>
            <section class="wave-container">
                <div id="landing" class="green-background">
                    <div></div>
                </div>
                <div class="wave">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 200"><path fill="#00CBA9" fill-opacity="0.7" d="M0,32L48,64C96,96,192,160,288,170.7C384,181,480,139,576,101.3C672,64,768,32,864,58.7C960,85,1056,171,1152,176C1248,181,1344,107,1392,69.3L1440,32L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>
                </div>
            </section>
            <section id="content-1">
                <article class="left half-width">
                    <h1>Detailed Stock Analysis</h1>
                    <p class="full-width left padding-bottom padding-top margin-top">DTrade empowers its users with up-to-date stock information and machine learning analysis of said data. The information provided by our algorithm is used to power projection displays, and user-specific portfolio sizing recommendations based on the Kelly Criterion.</p>
                </article>
                <article class="right half-width">
                    <h1>Automated Stock Trading</h1>
                    <p class="full-width right padding-bottom padding-top margin-top">Our system integrates with Robinhood in order to execute trades on our user's behalf. Trades can be set to maintain the Kelly-Optimal portfolio ratio for a given stock.</p>
                </article>
            </section>
            <section class="wave-container">
                <div id="content-2-wave" class="wave">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#00cba9" fill-opacity="0.7" d="M0,160L30,181.3C60,203,120,245,180,240C240,235,300,181,360,160C420,139,480,149,540,181.3C600,213,660,267,720,272C780,277,840,235,900,208C960,181,1020,171,1080,160C1140,149,1200,139,1260,144C1320,149,1380,171,1410,181.3L1440,192L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z"></path></svg>
                </div>
                <div id="content-2" class="padding green-background">
                    <article class="center">
                        <h1 class="text-left underline-left">Choose any stocks <br /> for your watchlist</h1>
                        <div class="center">
                            <img src="/images/aapl-amd-sbux.png" width="100%" />
                        </div>
                        <p class="text-left right underline-right">They will be kept up-to-date <br /> and automatically analyzed daily.</p>
                    </article>
                    <aside>
                        <span id="slash-1"></span>
                        <span id="slash-2"></span>
                        <span id="slash-3"></span>
                    </aside>
                </div>
                <div class="wave">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#00cba9" fill-opacity="0.7" d="M0,96L26.7,106.7C53.3,117,107,139,160,176C213.3,213,267,267,320,245.3C373.3,224,427,128,480,85.3C533.3,43,587,53,640,74.7C693.3,96,747,128,800,133.3C853.3,139,907,117,960,138.7C1013.3,160,1067,224,1120,229.3C1173.3,235,1227,181,1280,149.3C1333.3,117,1387,107,1413,101.3L1440,96L1440,0L1413.3,0C1386.7,0,1333,0,1280,0C1226.7,0,1173,0,1120,0C1066.7,0,1013,0,960,0C906.7,0,853,0,800,0C746.7,0,693,0,640,0C586.7,0,533,0,480,0C426.7,0,373,0,320,0C266.7,0,213,0,160,0C106.7,0,53,0,27,0L0,0Z"></path></svg>
                </div>
            </section>
        </main>
    </body>
</html>
