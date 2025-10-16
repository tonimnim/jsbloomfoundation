<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Post: {{ $post->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            height: auto;
        }
        h1 {
            color: #1e40af;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .post-meta {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #3b82f6;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #2563eb;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e5e5;
        }
        .unsubscribe {
            color: #666;
            text-decoration: underline;
        }
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            .container {
                padding: 20px;
            }
            h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="color: #1e40af; margin: 0;">JS Bloom Foundation</h2>
            <p style="color: #666; margin: 5px 0;">Empowering Children, Youth, and the Elderly</p>
        </div>

        <h1>{{ $post->title }}</h1>

        <div class="post-meta">
            <strong>Category:</strong> {{ $post->category_name ?? 'General' }}<br>
            <strong>Author:</strong> {{ $post->author_name ?? 'JS Bloom Foundation Team' }}<br>
            <strong>Published:</strong> {{ \Carbon\Carbon::parse($post->published_at)->format('F d, Y') }}
        </div>

        <div class="content">
            <p>Dear {{ $subscriber->name ?? 'Valued Supporter' }},</p>
            
            <p>We're excited to share our latest blog post with you:</p>
            
            @if($post->excerpt)
                <p style="font-style: italic; color: #555; padding: 15px; background-color: #f9f9f9; border-left: 4px solid #3b82f6;">
                    {{ $post->excerpt }}
                </p>
            @endif

            <p style="text-align: center;">
                <a href="{{ url('/blog/' . $post->slug) }}" class="button">Read Full Article</a>
            </p>

            @if($post->featured_image)
                <p style="text-align: center;">
                    <img src="{{ url($post->featured_image) }}" alt="{{ $post->title }}" style="max-width: 100%; height: auto; border-radius: 5px;">
                </p>
            @endif

            <p>Thank you for your continued interest in our work. Your support helps us make a lasting impact in communities.</p>
            
            <p>Stay connected with us to receive updates about our programs, success stories, and ways you can make a difference.</p>
        </div>

        <div class="footer">
            <p>
                <strong>JS Bloom Foundation</strong><br>
                Lagos, Nigeria<br>
                <a href="{{ url('/') }}" style="color: #3b82f6;">www.jsbloomfoundation.org</a>
            </p>
            
            <p>
                You're receiving this email because you subscribed to our newsletter.<br>
                <a href="{{ $unsubscribeUrl }}" class="unsubscribe">Unsubscribe from our newsletter</a>
            </p>
            
            <p style="margin-top: 20px; color: #999;">
                © {{ date('Y') }} JS Bloom Foundation. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>