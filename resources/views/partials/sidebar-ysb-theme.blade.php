<style id="sidebar-ysb-theme">
        /* Yonetim sidebar — sade acik zemin, animasyonlar duruyor */
        @keyframes ysbIn {
            0% { opacity: 0; transform: translateY(8px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: reduce) {
            #sidebar.ysb, #sidebar.ysb * {
                animation: none !important;
                transition: none !important;
            }
        }

        #sidebar.ysb {
            width: 18rem !important;
            color: #44403c;
            border-right: 1px solid #e7e5e4 !important;
            /* Onceki sade acik arka plan — gosterissiz */
            background: #fafaf9 !important;
            box-shadow: none !important;
            overflow: hidden;
        }
        #sidebar.ysb::before,
        #sidebar.ysb::after {
            display: none !important;
            content: none !important;
        }
        #sidebar.ysb > * { position: relative; z-index: 1; }

        #sidebar.ysb .ysb-brand {
            padding: 1.15rem 1.1rem 1rem;
            border-bottom: 1px solid #f0f0ee;
            background: #ffffff;
        }
        #sidebar.ysb .ysb-brand-row {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            animation: ysbIn .45s cubic-bezier(.22,1,.36,1) both;
        }
        #sidebar.ysb .ysb-brand-mark {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            box-shadow: none;
            animation: none;
            overflow: hidden;
            position: relative;
        }
        #sidebar.ysb .ysb-brand-mark::after { display: none; }
        #sidebar.ysb .ysb-brand-mark img {
            width: 1.55rem;
            height: 1.55rem;
            object-fit: contain;
            mix-blend-mode: multiply;
            filter: none;
            position: relative;
            z-index: 1;
        }
        #sidebar.ysb .ysb-brand-title {
            font-family: Outfit, Inter, sans-serif;
            font-weight: 800;
            font-size: 0.95rem;
            letter-spacing: -.02em;
            color: #1c1917 !important;
            line-height: 1.15;
        }
        #sidebar.ysb .ysb-brand-sub {
            margin-top: .22rem;
            font-size: .62rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #c96a2b !important;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }
        #sidebar.ysb .ysb-brand-sub::before {
            content: "";
            width: .38rem;
            height: .38rem;
            border-radius: 999px;
            background: #22c55e;
            box-shadow: none;
            animation: none;
        }

        #sidebar.ysb .ysb-nav {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 0.95rem 0.8rem 1.1rem;
            scrollbar-width: thin;
            scrollbar-color: #e7e5e4 transparent;
        }
        #sidebar.ysb .ysb-nav::-webkit-scrollbar { width: 5px; }
        #sidebar.ysb .ysb-nav::-webkit-scrollbar-thumb {
            background: #e7e5e4;
            border-radius: 99px;
        }

        #sidebar.ysb .ysb-dash {
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: .78rem .85rem;
            margin-bottom: 1rem;
            border-radius: 0.95rem;
            text-decoration: none !important;
            position: relative;
            overflow: hidden;
            border: 1px solid #e7e5e4;
            background: #ffffff;
            box-shadow: none;
            transition: transform .28s cubic-bezier(.22,1,.36,1), border-color .2s, box-shadow .28s, background .2s;
            animation: ysbIn .45s cubic-bezier(.22,1,.36,1) .05s both;
        }
        #sidebar.ysb .ysb-dash::before { display: none; }
        #sidebar.ysb .ysb-dash:hover {
            transform: translateY(-1px);
            border-color: #fdba74;
            box-shadow: 0 4px 14px rgba(28,25,23,.05);
        }
        #sidebar.ysb .ysb-dash.is-active {
            border-color: #fdba74;
            background: #fff7ed;
            box-shadow: none;
        }
        #sidebar.ysb .ysb-dash-icon {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #78716c;
            background: #f5f5f4;
            border: 1px solid #e7e5e4;
            transition: transform .3s cubic-bezier(.22,1,.36,1), background .2s, color .2s, border-color .2s;
        }
        #sidebar.ysb .ysb-dash:hover .ysb-dash-icon {
            transform: scale(1.05);
            color: #c96a2b;
            background: #fff7ed;
            border-color: #fed7aa;
        }
        #sidebar.ysb .ysb-dash.is-active .ysb-dash-icon {
            color: #fff;
            background: #c96a2b;
            border-color: #c96a2b;
            box-shadow: none;
            animation: none;
        }
        #sidebar.ysb .ysb-dash-icon svg { width: 1.1rem; height: 1.1rem; }
        #sidebar.ysb .ysb-dash-title {
            display: block;
            font-family: Outfit, Inter, sans-serif;
            font-weight: 700;
            font-size: .86rem;
            letter-spacing: -.02em;
            color: #1c1917 !important;
        }
        #sidebar.ysb .ysb-dash-sub {
            display: block;
            margin-top: .12rem;
            font-size: .68rem;
            color: #a8a29e !important;
        }
        #sidebar.ysb .ysb-dash.is-active .ysb-dash-title { color: #9a3412 !important; }
        #sidebar.ysb .ysb-dash.is-active .ysb-dash-sub { color: #c2410c !important; }

        #sidebar.ysb .ysb-section-label {
            font-size: .62rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #a8a29e !important;
            padding: .1rem .5rem .5rem;
            animation: ysbIn .4s cubic-bezier(.22,1,.36,1) .08s both;
        }

        #sidebar.ysb .ysb-group {
            margin-bottom: .32rem;
            border-radius: 0.9rem;
            border: 1px solid transparent;
            background: transparent;
            transition: background .25s, border-color .25s, box-shadow .25s;
            animation: ysbIn .45s cubic-bezier(.22,1,.36,1) both;
            animation-delay: calc(.08s + var(--ysb-i, 0) * .05s);
        }
        #sidebar.ysb .ysb-group.is-open {
            background: #ffffff;
            border-color: #e7e5e4;
            box-shadow: 0 2px 10px rgba(28,25,23,.04);
        }
        #sidebar.ysb .ysb-group.is-active.is-open {
            background: #fffaf5;
            border-color: #fed7aa;
            box-shadow: none;
        }
        #sidebar.ysb .ysb-group-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .65rem .7rem;
            border: 0;
            border-radius: 0.9rem;
            background: transparent;
            cursor: pointer;
            text-align: left;
            color: #44403c !important;
            transition: background .2s, color .2s;
        }
        #sidebar.ysb .ysb-group-btn:hover {
            background: #f5f5f4;
            color: #1c1917 !important;
        }
        #sidebar.ysb .ysb-group.is-open .ysb-group-btn,
        #sidebar.ysb .ysb-group.is-active .ysb-group-btn {
            color: #9a3412 !important;
        }
        #sidebar.ysb .ysb-group-icon {
            width: 1.95rem;
            height: 1.95rem;
            border-radius: .6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #78716c;
            background: #f5f5f4;
            border: 1px solid #e7e5e4;
            transition: transform .3s cubic-bezier(.22,1,.36,1), background .2s, color .2s, border-color .2s;
        }
        #sidebar.ysb .ysb-group-icon svg { width: 1rem; height: 1rem; }
        #sidebar.ysb .ysb-group-btn:hover .ysb-group-icon {
            transform: scale(1.06);
            color: #c96a2b;
            background: #fff7ed;
            border-color: #fed7aa;
        }
        #sidebar.ysb .ysb-group.is-open .ysb-group-icon {
            color: #fff;
            background: #c96a2b;
            border-color: #c96a2b;
            box-shadow: none;
            transform: scale(1.04);
        }
        #sidebar.ysb .ysb-group-label {
            flex: 1;
            font-family: Outfit, Inter, sans-serif;
            font-weight: 600;
            font-size: .82rem;
            letter-spacing: -.01em;
        }
        #sidebar.ysb .ysb-group-meta {
            display: flex;
            align-items: center;
            gap: .35rem;
            flex-shrink: 0;
        }
        #sidebar.ysb .ysb-count {
            min-width: 1.15rem;
            height: 1.15rem;
            padding: 0 .3rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .62rem;
            font-weight: 800;
            color: #a8a29e;
            background: #f5f5f4;
            border: 1px solid #e7e5e4;
            transition: .2s;
        }
        #sidebar.ysb .ysb-group.is-open .ysb-count {
            color: #c2410c;
            background: #ffedd5;
            border-color: #fed7aa;
        }
        #sidebar.ysb .ysb-chevron {
            width: .95rem;
            height: .95rem;
            color: #a8a29e;
            transition: transform .35s cubic-bezier(.22,1,.36,1), color .2s;
        }
        #sidebar.ysb .ysb-group.is-open .ysb-chevron {
            transform: rotate(180deg);
            color: #c96a2b;
        }

        #sidebar.ysb .ysb-panel {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows .36s cubic-bezier(.22,1,.36,1);
            padding: 0 .3rem;
        }
        #sidebar.ysb .ysb-group.is-open .ysb-panel {
            grid-template-rows: 1fr;
            padding-bottom: .4rem;
        }
        #sidebar.ysb .ysb-panel-inner {
            overflow: hidden;
            min-height: 0;
            opacity: 0;
            transform: translateY(-4px);
            transition: opacity .25s ease .04s, transform .3s cubic-bezier(.22,1,.36,1);
        }
        #sidebar.ysb .ysb-group.is-open .ysb-panel-inner {
            opacity: 1;
            transform: translateY(0);
        }
        #sidebar.ysb .ysb-list {
            list-style: none;
            margin: .1rem 0 0 .95rem;
            padding: .08rem 0 .12rem .45rem;
            border-left: 2px solid #f0f0ee;
        }
        #sidebar.ysb .ysb-group.is-open .ysb-list {
            border-left-color: #fed7aa;
        }
        #sidebar.ysb .ysb-list li {
            opacity: 0;
            transform: translateX(-8px);
            transition: opacity .25s ease, transform .3s cubic-bezier(.22,1,.36,1);
        }
        #sidebar.ysb .ysb-group.is-open .ysb-list li {
            opacity: 1;
            transform: translateX(0);
        }
        #sidebar.ysb .ysb-group.is-open .ysb-list li:nth-child(1) { transition-delay: .04s; }
        #sidebar.ysb .ysb-group.is-open .ysb-list li:nth-child(2) { transition-delay: .07s; }
        #sidebar.ysb .ysb-group.is-open .ysb-list li:nth-child(3) { transition-delay: .1s; }
        #sidebar.ysb .ysb-group.is-open .ysb-list li:nth-child(4) { transition-delay: .13s; }
        #sidebar.ysb .ysb-group.is-open .ysb-list li:nth-child(5) { transition-delay: .16s; }
        #sidebar.ysb .ysb-group.is-open .ysb-list li:nth-child(6) { transition-delay: .19s; }

        #sidebar.ysb .ysb-link {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .48rem .7rem;
            margin: .1rem 0 .1rem .35rem;
            border-radius: .6rem;
            text-decoration: none !important;
            color: #57534e !important;
            font-size: .79rem;
            font-weight: 500;
            transition: background .2s, color .2s, transform .25s cubic-bezier(.22,1,.36,1);
        }
        #sidebar.ysb .ysb-link:hover {
            color: #1c1917 !important;
            background: #f5f5f4;
            transform: translateX(3px);
        }
        #sidebar.ysb .ysb-link.is-active {
            color: #fff !important;
            font-weight: 700;
            background: #c96a2b;
            box-shadow: none;
            animation: none;
            transform: translateX(2px);
        }
        #sidebar.ysb .ysb-dot {
            width: .36rem;
            height: .36rem;
            border-radius: 999px;
            background: #d6d3d1;
            flex-shrink: 0;
            transition: .2s;
        }
        #sidebar.ysb .ysb-link:hover .ysb-dot { background: #c96a2b; }
        #sidebar.ysb .ysb-link.is-active .ysb-dot {
            background: #fff;
            box-shadow: none;
            animation: none;
        }
        #sidebar.ysb .ysb-link-text {
            flex: 1;
            min-width: 0;
            font-family: Outfit, Inter, sans-serif;
            letter-spacing: -.01em;
        }
        #sidebar.ysb .ysb-badge {
            min-width: 1.2rem;
            height: 1.15rem;
            padding: 0 .35rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .62rem;
            font-weight: 800;
            color: #b45309;
            background: #fef3c7;
            border: 1px solid #fde68a;
            animation: none;
        }
        #sidebar.ysb .ysb-link.is-active .ysb-badge {
            color: #fff;
            background: rgba(255,255,255,.22);
            border-color: transparent;
        }

        #sidebar.ysb .ysb-footer {
            border-top: 1px solid #f0f0ee;
            background: #ffffff;
            padding: .9rem .95rem;
            animation: ysbIn .45s cubic-bezier(.22,1,.36,1) .15s both;
        }
        #sidebar.ysb .ysb-footer-row {
            display: flex;
            align-items: center;
            gap: .65rem;
        }
        #sidebar.ysb .ysb-avatar {
            width: 2.3rem;
            height: 2.3rem;
            border-radius: .7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-family: Outfit, Inter, sans-serif;
            font-weight: 800;
            font-size: .7rem;
            color: #c96a2b !important;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            box-shadow: none;
            transition: transform .28s cubic-bezier(.22,1,.36,1);
        }
        #sidebar.ysb .ysb-footer-row:hover .ysb-avatar {
            transform: scale(1.04);
            box-shadow: none;
        }
        #sidebar.ysb .ysb-user-name {
            font-family: Outfit, Inter, sans-serif;
            font-weight: 700;
            font-size: .8rem;
            color: #1c1917 !important;
            max-width: 7.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        #sidebar.ysb .ysb-user-role {
            margin-top: .12rem;
            font-size: .62rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: #c96a2b !important;
        }
        #sidebar.ysb .ysb-footer-actions {
            margin-left: auto;
            display: flex;
            gap: .3rem;
        }
        #sidebar.ysb .ysb-icon-btn {
            width: 2.05rem;
            height: 2.05rem;
            border-radius: .6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e7e5e4;
            background: #fafaf9;
            color: #78716c !important;
            cursor: pointer;
            text-decoration: none;
            padding: 0;
            transition: transform .25s cubic-bezier(.22,1,.36,1), background .2s, color .2s, border-color .2s;
        }
        #sidebar.ysb .ysb-icon-btn:hover {
            color: #c96a2b !important;
            background: #fff7ed;
            border-color: #fed7aa;
            transform: translateY(-1px);
            box-shadow: none;
        }
        #sidebar.ysb .ysb-icon-btn:active { transform: scale(.96); }
        #sidebar.ysb .ysb-icon-btn svg { width: .95rem; height: .95rem; }
    </style>