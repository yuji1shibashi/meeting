<header>
    <img class="foxhound" src="{{ asset('image/foxhound.png') }}" alt="">
    <div class="d_ib header_title">Time Reservation U</div>
    <div class="d_ib nav_area">
        <ul class="header_nav">
            <li>
                <a href="{{ url('/reservation') }}">日別会議室予約一覧</a>
            </li>
            @guest
                <li>
                    <a href="{{ url('/login') }}">ログイン</a>
                </li>
            @endguest
            @auth
                <li>
                    <a href="{{ url('/account_list') }}">アカウントマスタ</a>
                </li>
                <li>
                    <a href="{{ url('/meeting_room') }}">会議室マスタ</a>
                </li>
                <li>
                    <a href="{{ url('/logout') }}">ログアウト</a>
                </li>
                <li class="fr">
                    <p>{{ Auth::user()->name }}</p>
                </li>
            @endauth
        </ul>
    </div>
</header>

<style>
    html, body {
        margin: 0px;
        padding: 0px;
    }
    header {
        height: 55px;
        background-color: #252525;
        color: #ffffff;
        width: 100%;
    }
    .header_title {
        margin-left: 5px;
    }

    .header_nav {
        list-style-type: none;
        padding-left: 80px;
    }

    .header_nav li {
        line-height: 50px;
        font-size: 20px;
        text-align: center;
        display: inline-block;
        cursor: pointer;
        margin: 0px 20px;
    }

    .header_nav a, .header_nav p {
        text-decoration: none;
        color: #fff;
        font-weight: bold;
    }

    .nav_area {
        width: 87%;
    }

    .foxhound {
        width: 45px;
        margin-left: 30px;
    }
</style>