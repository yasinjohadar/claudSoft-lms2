<div class="faqs-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <!-- Left Side - FAQs Accordion -->
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="faq-content">
                    <div class="section-header mb-4">
                        <h2 class="section-title">
                            <i class="fa-solid fa-circle-question"></i>
                            الأسئلة الشائعة
                        </h2>
                        <p class="section-subtitle">إجابات سريعة على الأسئلة الأكثر شيوعاً</p>
                    </div>

                    <div class="accordion" id="faqAccordion">
                        @forelse($faqs as $index => $faq)
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#faq{{ $faq->id }}" 
                                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                            aria-controls="faq{{ $faq->id }}">
                                        <span class="question-icon">
                                            <i class="fa-solid fa-question-circle"></i>
                                        </span>
                                        <span class="question-text">{{ $faq->question }}</span>
                                    </button>
                                </h3>
                                <div id="faq{{ $faq->id }}" 
                                     class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                                     data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <div class="answer-content">
                                            {!! nl2br(e($faq->answer)) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state text-center py-5">
                                <i class="fa-solid fa-circle-question fa-3x text-muted mb-3"></i>
                                <p class="text-muted">لا توجد أسئلة شائعة متاحة حالياً</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Side - Company Info -->
            <div class="col-lg-6">
                <div class="company-info-wrapper">
                    <div class="company-info">
                        <div class="company-header">
                            <h3 class="company-title">
                                <i class="fa-solid fa-building"></i>
                                عن كلاودسوفت
                            </h3>
                        </div>
                        <div class="company-content">
                            <p class="company-description">
                                <strong>كلاودسوفت</strong> هي منصة تعليمية رائدة متخصصة في تقديم أفضل الدورات والكورسات التدريبية في مختلف المجالات التقنية والتكنولوجية. نسعى لتأهيل المتدربين والمطورين لسوق العمل باحترافية عالية من خلال محتوى تعليمي عالي الجودة.
                            </p>
                            
                            <div class="services-section">
                                <h4 class="services-title">
                                    <i class="fa-solid fa-briefcase"></i>
                                    خدماتنا
                                </h4>
                                <ul class="services-list">
                                    <li>
                                        <i class="fa-solid fa-check-circle"></i>
                                        <span>دورات تدريبية احترافية في البرمجة والتطوير</span>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-check-circle"></i>
                                        <span>كورسات في تصميم الويب والجرافيك</span>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-check-circle"></i>
                                        <span>تدريب على أحدث التقنيات والأدوات</span>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-check-circle"></i>
                                        <span>شهادات معتمدة مع إتمام الكورسات</span>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-check-circle"></i>
                                        <span>دعم فني ومتابعة مستمرة للمتدربين</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="courses-section">
                                <h4 class="courses-title">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                    الكورسات المتاحة
                                </h4>
                                <p class="courses-description">
                                    نقدم مجموعة واسعة من الكورسات في مجالات متعددة تشمل:
                                </p>
                                <div class="courses-tags">
                                    <span class="course-tag">Laravel</span>
                                    <span class="course-tag">Vue.js</span>
                                    <span class="course-tag">React</span>
                                    <span class="course-tag">Node.js</span>
                                    <span class="course-tag">Python</span>
                                    <span class="course-tag">Kotlin</span>
                                    <span class="course-tag">WordPress</span>
                                    <span class="course-tag">UI/UX Design</span>
                                    <span class="course-tag">الأمن السيبراني</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* FAQs Section */
.faqs-section {
    background: #f8f9fa;
    position: relative;
    overflow: hidden;
}

.faqs-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: rgba(5, 85, 162, 0.03);
    pointer-events: none;
}

/* Company Info Wrapper */
.company-info-wrapper {
    position: relative;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.company-info {
    background: #ffffff;
    border-radius: 7px;
    padding: 30px;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
    width: 100%;
}

.company-header {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
}

.company-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.company-title i {
    color: var(--main-Color);
    font-size: 1.5rem;
}

.company-content {
    line-height: 1.8;
}

.company-description {
    font-size: 1rem;
    color: #555;
    margin-bottom: 25px;
    text-align: right;
}

.company-description strong {
    color: var(--secondary-Color);
    font-size: 1.1rem;
}

.services-section,
.courses-section {
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid #e9ecef;
}

.services-title,
.courses-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.services-title i,
.courses-title i {
    color: var(--main-Color);
}

.services-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.services-list li {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 12px;
    padding-right: 10px;
    text-align: right;
}

.services-list li i {
    color: var(--main-Color);
    font-size: 1rem;
    margin-top: 4px;
    flex-shrink: 0;
}

.services-list li span {
    color: #666;
    font-size: 0.95rem;
    flex: 1;
}

.courses-description {
    color: #666;
    font-size: 0.95rem;
    margin-bottom: 15px;
    text-align: right;
}

.courses-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.course-tag {
    background: var(--secondary-Color);
    color: #ffffff;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.course-tag:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Section Header */
.section-header {
    margin-bottom: 30px;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.section-title i {
    color: var(--secondary-Color);
    font-size: 2rem;
    animation: pulse-icon 2s ease-in-out infinite;
}

@keyframes pulse-icon {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.section-subtitle {
    color: #666;
    font-size: 1.1rem;
    margin: 0;
}

/* Accordion Styles */
.accordion {
    --bs-accordion-border-color: transparent;
    --bs-accordion-border-radius: 15px;
    --bs-accordion-inner-border-radius: 15px;
}

.accordion-item {
    border: none;
    margin-bottom: 15px;
    border-radius: 15px !important;
    overflow: hidden;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
    background: #ffffff;
    transition: all 0.3s ease;
}

.accordion-item:hover {
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

.accordion-button {
    background: #ffffff;
    color: var(--secondary-Color);
    font-weight: 600;
    padding: 20px 25px;
    border: none;
    font-size: 1.1rem;
    box-shadow: none;
    display: flex;
    align-items: center;
    gap: 15px;
}

.accordion-button:not(.collapsed) {
    background: var(--secondary-Color);
    color: #ffffff;
    box-shadow: none;
}

.accordion-button:not(.collapsed) .question-icon {
    color: #ffffff;
}

.accordion-button:focus {
    box-shadow: none;
    border: none;
    outline: none;
}

.accordion-button::after {
    margin-right: auto;
    margin-left: 0;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%230555a2'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
}

.accordion-button:not(.collapsed)::after {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
}

.question-icon {
    font-size: 1.5rem;
    color: var(--secondary-Color);
    transition: all 0.3s ease;
}

.question-text {
    flex: 1;
    text-align: right;
    line-height: 1.6;
}

.accordion-body {
    padding: 25px;
    background: #ffffff;
    color: #555;
    line-height: 1.8;
}

.answer-content {
    font-size: 1rem;
    color: #666;
}

/* Empty State */
.empty-state {
    padding: 60px 20px;
}

.empty-state i {
    opacity: 0.3;
}

/* Responsive */
@media (max-width: 992px) {
    .company-info {
        padding: 25px;
    }

    .company-title {
        font-size: 1.5rem;
    }

    .section-title {
        font-size: 2rem;
    }
}

@media (max-width: 768px) {
    .section-title {
        font-size: 1.75rem;
    }

    .section-subtitle {
        font-size: 1rem;
    }

    .accordion-button {
        padding: 15px 20px;
        font-size: 1rem;
    }

    .accordion-body {
        padding: 20px;
    }

    .company-info {
        padding: 20px;
    }

    .company-title {
        font-size: 1.3rem;
    }

    .services-title,
    .courses-title {
        font-size: 1.1rem;
    }

    .course-tag {
        font-size: 0.8rem;
        padding: 6px 12px;
    }
}
</style>

