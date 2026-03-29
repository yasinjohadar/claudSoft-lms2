<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أنواع البيانات (Data Types) — PHP Diploma</title>

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">

    <!-- Prism.js CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">

    <!-- Custom Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>

    <!-- Theme Toggle -->
    <button class="theme-toggle" onclick="toggleTheme()" aria-label="تبديل الوضع">
        <span class="icon moon">🌙</span>
        <span class="icon sun">☀️</span>
        <span class="text">الوضع</span>
    </button>

    <!-- Glow Effects -->
    <div class="glow-blob blob-1"></div>
    <div class="glow-blob blob-2"></div>
    <div class="glow-blob blob-3"></div>

    <div class="container">

        <!-- HEADER -->
        <header>
            <div class="header-tag">PHP Basics — Lesson 05</div>
            <h1>أنواع البيانات <em>(Data Types)</em></h1>
            <p class="header-desc">فهم الأنواع الأساسية للبيانات في PHP وكيفية التعامل معها بطريقة احترافية.</p>
        </header>

        <!-- SECTION 1 -->
        <section class="content-section" style="animation-delay: 0.1s;">
            <div class="text-block">
                في البرمجة، كل قيمة لها نوع بيانات (Data Type) يحدد طريقة تعامل اللغة معها.
            </div>
            
            <div class="info-box info">
                <div class="info-box-title">🔸 ملاحظة مهمة (Loosely Typed)</div>
                <p>تعمل لغة PHP على تحديد النوع <strong>تلقائيًا</strong> بمجرد أن تعطي المتغير قيمة، لذلك تُسمى لغة مرنة الأنواع (Loosely Typed).</p>
            </div>

            <h2 class="section-title">الأنواع <span class="highlight">الأساسية</span> في PHP</h2>
            <ul class="styled-list">
                <li><strong>String</strong> (نص)</li>
                <li><strong>Integer</strong> (عدد صحيح)</li>
                <li><strong>Float / Double</strong> (عدد عشري)</li>
                <li><strong>Boolean</strong> (قيمة منطقية: true / false)</li>
                <li><strong>NULL</strong> (قيمة فارغة أو معدومة)</li>
                <li><strong>Array</strong> (مصفوفة)</li>
                <li><strong>Object</strong> (كائن)</li>
                <li><strong>Resource</strong> (مورد خارجي)</li>
            </ul>
        </section>

        <!-- SECTION 2: String & Integer -->
        <section class="content-section" style="animation-delay: 0.2s;">
            <h2 class="section-title">1️⃣ String <span class="highlight-green">(سلسلة نصية)</span></h2>

            <div class="text-block">
                هو أي نص يُكتب بين علامات الاقتباس.
            </div>

            <div class="code-block">
                <div class="code-header">
                    <div class="code-dots"><span></span><span></span><span></span></div>
                    <span class="code-lang">PHP</span>
                </div>
                <pre><code class="language-php">&lt;?php
  $name = "أحمد";
  $message = 'أهلا بك في PHP';
?&gt;</code></pre>
            </div>

            <div class="info-box success">
                <div class="info-box-title">✅ ملاحظات مهمة</div>
                <p>✔️ يمكن استخدام علامات الاقتباس المفردة <code class="inline-code">'</code> أو المزدوجة <code class="inline-code">"</code><br>
                ✔️ علامات الاقتباس المزدوجة تسمح بدمج المتغيرات داخلها مباشرة.</p>
            </div>

            <h3 class="subsection-title"><span class="icon">🔥</span> إضافة احترافية (الهروب - Escape Characters)</h3>
            <div class="code-block">
                <div class="code-header">
                    <div class="code-dots"><span></span><span></span><span></span></div>
                    <span class="code-lang">PHP</span>
                </div>
                <pre><code class="language-php">&lt;?php
  echo "He said \"Hello\"";
  echo 'It\'s PHP';
?&gt;</code></pre>
            </div>

            <hr>

            <h2 class="section-title" style="margin-top: 40px;">2️⃣ Integer <span class="highlight-purple">(عدد صحيح)</span></h2>

            <div class="code-block">
                <div class="code-header">
                    <div class="code-dots"><span></span><span></span><span></span></div>
                    <span class="code-lang">PHP</span>
                </div>
                <pre><code class="language-php">&lt;?php
  $age = 25;
  $year = 2025;
  $temperature = -5;
?&gt;</code></pre>
            </div>

            <div class="info-box success">
                <div class="info-box-title">✅ ملاحظات</div>
                <p>يمكن أن يكون العدد الصحيح موجباً أو سالباً، ولكن لا يحتوي على فاصلة عشرية.</p>
            </div>

            <h3 class="subsection-title"><span class="icon">🔥</span> إضافة احترافية: وظيفة is_int()</h3>
            <div class="code-block">
                <div class="code-header">
                    <div class="code-dots"><span></span><span></span><span></span></div>
                    <span class="code-lang">PHP</span>
                </div>
                <pre><code class="language-php">&lt;?php
  var_dump(is_int($age)); // النتيجة: true
?&gt;</code></pre>
            </div>
        </section>

        <!-- SECTION 3: Float & Boolean -->
        <section class="content-section" style="animation-delay: 0.3s;">
            <h2 class="section-title">3️⃣ Float <span class="highlight-orange">(عدد عشري)</span></h2>

            <div class="code-block">
                <div class="code-header">
                    <div class="code-dots"><span></span><span></span><span></span></div>
                    <span class="code-lang">PHP</span>
                </div>
                <pre><code class="language-php">&lt;?php
  $price = 99.99;
  $gpa = 3.75;
?&gt;</code></pre>
            </div>

            <div class="info-box warning">
                <div class="info-box-title">⚠️ دقة الأعداد العشرية</div>
                <p>بسبب طريقة حساب الأعداد العشرية في الحواسيب، قد تجد نتائج غير متوقعة عند الجمع الدقيق:<br>
                <code class="inline-code">$x = 0.1 + 0.2;</code> <br>
                <code class="inline-code">var_dump($x);</code> // قد يعطي 0.30000000000000004 بدلاً من 0.3</p>
            </div>

            <hr>

            <h2 class="section-title" style="margin-top: 40px;">4️⃣ Boolean <span class="highlight-red">(مَنطِقي)</span></h2>

            <div class="code-block">
                <div class="code-header">
                    <div class="code-dots"><span></span><span></span><span></span></div>
                    <span class="code-lang">PHP</span>
                </div>
                <pre><code class="language-php">&lt;?php
  $isOnline = true;
  $isAvailable = false;

  // استخدام عملي:
  if ($isOnline) {
      echo "المستخدم متصل";
  }
?&gt;</code></pre>
            </div>

            <div class="info-box info">
                <div class="info-box-title">🧠 ملاحظة احترافية (Truthy/Falsy)</div>
                <p>يتم تحويل بعض القيم تلقائياً في الشروط المنطقية. مثال باستخدام <code class="inline-code">var_dump()</code>:</p>
            </div>
            <div class="code-block">
                <div class="code-header">
                    <div class="code-dots"><span></span><span></span><span></span></div>
                    <span class="code-lang">PHP</span>
                </div>
                <pre><code class="language-php">&lt;?php
  var_dump((bool)0);     // false
  var_dump((bool)"");    // false
  var_dump((bool)1);     // true
?&gt;</code></pre>
            </div>
        </section>

        <!-- SECTION 4: Array, NULL, and Type Check -->
        <section class="content-section" style="animation-delay: 0.4s;">
            <h2 class="section-title">5️⃣ NULL & 6️⃣ <span class="highlight">Array</span></h2>
            
            <div class="subsection-title"><span class="icon">⚪</span> NULL (القيمة المعدومة)</div>
            <div class="code-block">
                <div class="code-header">
                    <div class="code-dots"><span></span><span></span><span></span></div>
                    <span class="code-lang">PHP</span>
                </div>
                <pre><code class="language-php">&lt;?php
  $user = null;

  if (is_null($user)) {
      echo "لا توجد قيمة";
  }
?&gt;</code></pre>
            </div>

            <div class="subsection-title"><span class="icon">🟧</span> Array (المصفوفة)</div>
            <div class="code-block">
                <div class="code-header">
                    <div class="code-dots"><span></span><span></span><span></span></div>
                    <span class="code-lang">PHP</span>
                </div>
                <pre><code class="language-php">&lt;?php
  $colors = ["أحمر", "أزرق", "أخضر"];
  echo $colors[0]; // يطبع: أحمر

  // إضافة احترافية: Associative Array
  $user = [
      "name" =&gt; "أحمد",
      "age" =&gt; 25
  ];
  
  echo $user["name"];
?&gt;</code></pre>
            </div>

            <hr>

            <h2 class="section-title" style="margin-top: 40px;">🔍 كيف أعرف نوع المتغير؟</h2>

            <div class="text-block">
                يمكن معرفة النوع من خلال الدوال <code class="inline-code">gettype()</code> و الأداة الاحترافية <code class="inline-code">var_dump()</code>.
            </div>

            <div class="code-block">
                <div class="code-header">
                    <div class="code-dots"><span></span><span></span><span></span></div>
                    <span class="code-lang">PHP</span>
                </div>
                <pre><code class="language-php">&lt;?php
  $score = 95;
  echo gettype($score); // يطبع: integer

  // الطريقة الاحترافية (var_dump):
  var_dump($score); // يطبع وتفصل: int(95)
?&gt;</code></pre>
            </div>
        </section>

        <!-- SECTION 5: Professional Additions -->
        <section class="content-section" style="animation-delay: 0.5s;">
            <h2 class="section-title">احتراف متقدم: <span class="highlight-red">Type Casting & Comparisons</span></h2>

            <h3 class="subsection-title"><span class="icon">🔥</span> التحويل القسري للأنواع (Type Casting)</h3>
            <div class="code-block">
                <div class="code-header">
                    <div class="code-dots"><span></span><span></span><span></span></div>
                    <span class="code-lang">PHP</span>
                </div>
                <pre><code class="language-php">&lt;?php
  $number = "10";

  $intNum = (int)$number;
  $floatNum = (float)$number;
  $stringNum = (string)$number;
?&gt;</code></pre>
            </div>

            <h3 class="subsection-title"><span class="icon">🔄</span> المقارنة المرنة مقابل المقارنة الصارمة</h3>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>المعامل</th>
                        <th>الوصف</th>
                        <th>أمثلة باستخدام <code class="inline-code">var_dump()</code></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><code class="inline-code">==</code></strong></td>
                        <td>مقارنة بالقيمة <strong>فقط</strong>.</td>
                        <td><code class="inline-code">var_dump(5 == "5"); // true</code></td>
                    </tr>
                    <tr>
                        <td><strong><code class="inline-code">===</code></strong></td>
                        <td>مقارنة بالقيمة <strong>والنوع</strong>.</td>
                        <td><code class="inline-code">var_dump(5 === "5"); // false</code></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="info-box error">
                <div class="info-box-title">⚠️ محاذير للمبتدئين</div>
                <p>
                    <strong>"20" + 5:</strong> قد يجبر اللغة على التحويل لنوع رقمي وهذا سلوك خطير.<br>
                    <strong>استخدام Boolean في عمليات حسابية:</strong> يعطي نتائج غير متوقعة.<br>
                    <strong>الخلط بين النص والعدد في المقارنات:</strong> يؤدي لأخطاء منطقية، لذا يُفضل الاهتمام بالتحويل الصحيح (Casting) والمقارنة المزدوجة والمقيدة بـ <code class="inline-code">===</code>.
                </p>
            </div>
        </section>

        <!-- SECTION 6: Exercise -->
        <section class="content-section" style="animation-delay: 0.6s;">
            <h2 class="section-title">تمرين <span class="highlight">الأنواع</span> 🎯</h2>

            <div class="info-box info">
                <div class="info-box-title">❓ التحدي</div>
                <p>أنشئ متغيرات تحتوي على الاسم، العمر، المعدل (GPA)، وهل أنت طالب؟ ومتغير فارغ. ثم أطبع نوع كل متغير باستخدام <code class="inline-code">gettype()</code>.</p>
            </div>

            <div class="info-box success">
                <div class="info-box-title">✅ الحل:</div>
            </div>

            <div class="code-block">
                <div class="code-header">
                    <div class="code-dots"><span></span><span></span><span></span></div>
                    <span class="code-lang">PHP</span>
                </div>
                <pre><code class="language-php">&lt;?php
  $name = "أحمد";
  $age = 18;
  $gpa = 92.5;
  $isStudent = true;
  $emptyValue = null;

  echo gettype($name) . "&lt;br&gt;";
  echo gettype($age) . "&lt;br&gt;";
  echo gettype($gpa) . "&lt;br&gt;";
  echo gettype($isStudent) . "&lt;br&gt;";
  echo gettype($emptyValue);
?&gt;</code></pre>
            </div>
        </section>

        <!-- خلاصة -->
        <section class="content-section" style="animation-delay: 0.7s;">
            <div class="info-box success">
                <div class="info-box-title">💎 الخلاصة الاحترافية</div>
                <p>
                    ✔️ لغة PHP تحدد النوع تلقائيًا بمجرد وضع القيمة.<br>
                    ✔️ ينبغي لك فهم الفروقات الدقيقة بين الأنواع لتجنب الأخطاء المنطقية.<br>
                    ✔️ استخدام <strong>Type Casting</strong> للمتغيرات القادمة من المتصفح أمر في غاية الأهمية.<br>
                    ✔️ استخدم المقارنة الصارمة <code class="inline-code">===</code> دائمًا تقريبًا لتجنب المفاجآت.<br>
                    ✔️ دالة <code class="inline-code">var_dump()</code> هي السلاح السري لكل مبرمج PHP قوي.
                </p>
            </div>
        </section>

        <!-- Navigation -->
        <nav class="lesson-nav">
            <a href="print_and_echo.html" class="nav-link">
                <span class="arrow prev">←</span>
                <div>
                    <div class="label">الدرس السابق</div>
                    <span>طباعة البيانات (echo و print)</span>
                </div>
            </a>
            <a href="constants.html" class="nav-link">
                <div>
                    <div class="label">الدرس التالي</div>
                    <span>الثوابت والكلمات المحجوزة</span>
                </div>
                <span class="arrow next">→</span>
            </a>
        </nav>

        <footer>
            <strong>PHP Professional Diploma</strong> — أنواع البيانات (Data Types)
        </footer>

    </div>

    <!-- Scripts (Prism & Custom) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup-templating.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>

    <script>
        // ─── THEME TOGGLE ───
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('php-diploma-theme', newTheme);
        }

        (function () {
            const savedTheme = localStorage.getItem('php-diploma-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();

        // ─── PRISM.JS & COPY BUTTON ───
        function initCodeBlocks() {
            try {
                if (typeof Prism !== 'undefined') Prism.highlightAll();
            } catch (e) {
                console.error("Prism error:", e);
            }

            document.querySelectorAll('.code-block').forEach(block => {
                if (block.querySelector('.copy-btn')) return;

                const header = block.querySelector('.code-header');
                const target = header || block;

                const copyBtn = document.createElement('button');
                copyBtn.className = 'copy-btn';
                copyBtn.innerHTML = '<span class="copy-icon">📋</span> <span class="btn-text">نسخ</span>';
                target.appendChild(copyBtn);

                copyBtn.addEventListener('click', async function () {
                    const codeEl = block.querySelector('code');
                    const code = codeEl.innerText.trim();

                    try {
                        await navigator.clipboard.writeText(code);
                        const originalHTML = copyBtn.innerHTML;
                        copyBtn.innerHTML = '<span class="copy-icon">✅</span> <span class="btn-text">تم النسخ</span>';
                        copyBtn.classList.add('copied');
                        
                        setTimeout(() => {
                            copyBtn.innerHTML = originalHTML;
                            copyBtn.classList.remove('copied');
                        }, 2000);
                    } catch (err) {
                        console.error('Failed to copy: ', err);
                    }
                });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCodeBlocks);
        } else {
            initCodeBlocks();
        }
    </script>

</body>

</html>
