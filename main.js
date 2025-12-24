// DarkSide Hackers - Main JavaScript File
// Comprehensive functionality for all pages

class DarkSideHackers {
    constructor() {
        this.currentLanguage = 'en';
        this.translations = {};
        this.init();
    }

    init() {
        this.setupMatrixBackground();
        this.setupAnimations();
        this.setupForms();
        this.setupNavigation();
        this.setupCounters();
        this.setupPricingCalculator();
        this.setupTeamModals();
        this.setupFAQ();
        this.setupTicketTracker();
        this.setupTestimonialSlider();
        this.setupLanguageTranslator();
        this.initializePageSpecificFeatures();
    }

    // Matrix Background Effect
    setupMatrixBackground() {
        if (document.getElementById('matrix-bg')) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            document.getElementById('matrix-bg').appendChild(canvas);

            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;

            const chars = '01アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン';
            const fontSize = 14;
            const columns = canvas.width / fontSize;
            const drops = Array(Math.floor(columns)).fill(1);

            function drawMatrix() {
                ctx.fillStyle = 'rgba(10, 10, 10, 0.04)';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                ctx.fillStyle = '#00ff41';
                ctx.font = fontSize + 'px monospace';

                for (let i = 0; i < drops.length; i++) {
                    const text = chars[Math.floor(Math.random() * chars.length)];
                    ctx.fillText(text, i * fontSize, drops[i] * fontSize);

                    if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                        drops[i] = 0;
                    }
                    drops[i]++;
                }
            }

            setInterval(drawMatrix, 35);

            window.addEventListener('resize', () => {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            });
        }
    }

    // Animation Setup
    setupAnimations() {
        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all cards and sections
        document.querySelectorAll('.card-hover, .service-icon, .team-avatar').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });

        // Typewriter effect for hero text
        if (document.querySelector('.neon-text')) {
            this.typewriterEffect();
        }
    }

    typewriterEffect() {
        const elements = document.querySelectorAll('.neon-text');
        elements.forEach(element => {
            const text = element.textContent;
            element.textContent = '';
            element.style.borderRight = '2px solid #00ff41';

            let i = 0;
            const typeInterval = setInterval(() => {
                if (i < text.length) {
                    element.textContent += text.charAt(i);
                    i++;
                } else {
                    clearInterval(typeInterval);
                    element.style.borderRight = 'none';
                }
            }, 100);
        });
    }

    // Form Handling
    setupForms() {
        // Service request form (index.html)
        const serviceForm = document.getElementById('service-form');
        if (serviceForm) {
            this.setupMultiStepForm(serviceForm);
        }

        // Contact form (contact.html)
        const contactForm = document.getElementById('contact-form');
        if (contactForm) {
            contactForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmission(contactForm, 'contact');
            });
        }

        // Service option selection
        document.querySelectorAll('.service-option input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                document.querySelectorAll('.service-option div').forEach(div => {
                    div.classList.remove('border-neon-green', 'bg-neon-green/10');
                    div.classList.add('border-text-muted');
                });
                
                const selectedDiv = e.target.closest('.service-option div');
                selectedDiv.classList.remove('border-text-muted');
                selectedDiv.classList.add('border-neon-green', 'bg-neon-green/10');
            });
        });
    }

    setupMultiStepForm(form) {
        let currentStep = 1;
        const totalSteps = 3;

        // Next step buttons
        document.getElementById('next-step-1')?.addEventListener('click', () => {
            if (this.validateStep(1)) {
                this.showStep(2);
                currentStep = 2;
            }
        });

        document.getElementById('next-step-2')?.addEventListener('click', () => {
            if (this.validateStep(2)) {
                this.showStep(3);
                currentStep = 3;
            }
        });

        // Previous step buttons
        document.getElementById('prev-step-2')?.addEventListener('click', () => {
            this.showStep(1);
            currentStep = 1;
        });

        document.getElementById('prev-step-3')?.addEventListener('click', () => {
            this.showStep(2);
            currentStep = 2;
        });

        // Form submission
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (this.validateStep(3)) {
                this.handleFormSubmission(form, 'service');
            }
        });
    }

    showStep(stepNumber) {
        document.querySelectorAll('.form-step').forEach(step => {
            step.classList.remove('active');
        });
        document.getElementById(`step-${stepNumber}`).classList.add('active');
    }

    validateStep(stepNumber) {
        const step = document.getElementById(`step-${stepNumber}`);
        const requiredFields = step.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#ff073a';
                isValid = false;
            } else {
                field.style.borderColor = '#00ff41';
            }
        });

        if (stepNumber === 1) {
            const serviceSelected = step.querySelector('input[name="service"]:checked');
            if (!serviceSelected) {
                alert('Please select a service');
                isValid = false;
            }
        }

        return isValid;
    }

    handleFormSubmission(form, type) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Validate all required fields
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        let firstInvalidField = null;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#ff073a';
                isValid = false;
                if (!firstInvalidField) firstInvalidField = field;
            } else {
                field.style.borderColor = '#00ff41';
            }
        });
        
        if (!isValid) {
            this.showNotification('Please fill in all required fields', 'error');
            if (firstInvalidField) {
                firstInvalidField.focus();
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'PROCESSING...';
        submitBtn.disabled = true;
        
        const feedbackDiv = document.getElementById('form-feedback');
        if (feedbackDiv) {
            feedbackDiv.textContent = '';
            feedbackDiv.className = 'mt-4 text-center text-sm';
        }

        // For contact form, submit to backend API
        if (type === 'contact') {
            // Prepare data for API submission
            const formPayload = {
                name: data.name,
                email: data.email,
                phone: data.phone || '',
                service: data.service,
                priority: data.priority,
                description: data.description,
                features: data['features[]'] ? (Array.isArray(data['features[]']) ? data['features[]'] : [data['features[]']]) : []
            };

            // Submit to backend PHP script
            // Use the same domain (works on both localhost and production)
            let apiUrl;
            
            // Check if we're in development (localhost) and use appropriate endpoint
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                // For local development, use the mock PHP server on port 8000
                apiUrl = 'http://localhost:8000/submit-form.php';
            } else if (window.location.protocol === 'file:') {
                // When opening HTML file directly (file:// protocol), use localhost
                apiUrl = 'http://localhost:8000/submit-form.php';
            } else {
                // For production, use the same domain
                apiUrl = `${window.location.protocol}//${window.location.hostname}${window.location.port ? ':' + window.location.port : ''}/submit-form.php`;
            }
            
            console.log('Submitting to:', apiUrl);
            
            fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formPayload)
            })
            .then(response => {
                // Check if response is OK
                if (!response.ok) {
                    // Handle HTTP errors (403, 404, 500, etc.)
                    if (response.status === 403) {
                        throw new Error('403 Forbidden: The server is blocking access to submit-form.php. Please check file permissions or server configuration.');
                    } else if (response.status === 404) {
                        throw new Error('404 Not Found: submit-form.php file not found on server. Please upload the PHP files to your hosting.');
                    } else {
                        throw new Error(`HTTP Error ${response.status}: ${response.statusText}`);
                    }
                }
                
                // Try to parse as JSON
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        // If not JSON, check if it's an error message
                        if (text.includes('Forbidden') || text.includes('Error') || text.includes('Warning')) {
                            throw new Error(`Server returned non-JSON response: ${text.substring(0, 100)}`);
                        }
                        // If it looks like success but not JSON, create a success response
                        return {
                            success: true,
                            ticketId: 'DSH-' + new Date().getFullYear() + '-' + Math.random().toString(36).substr(2, 6).toUpperCase(),
                            message: 'Form submitted successfully (non-JSON response)',
                            clientEmailSent: false,
                            adminEmailSent: false,
                            localStorage: true
                        };
                    }
                });
            })
            .then(result => {
                if (result.success) {
                    const ticketId = result.ticketId;
                    
                    // Store locally
                    localStorage.setItem('lastTicketId', ticketId);
                    localStorage.setItem('lastSubmission', JSON.stringify({
                        ...formPayload,
                        ticketId,
                        timestamp: new Date().toISOString(),
                        status: 'open'
                    }));

                    // Show appropriate message based on email status
                    if (result.clientEmailSent || result.adminEmailSent) {
                        // Emails were sent successfully
                        this.showNotification('Request submitted successfully! Check your email for confirmation.', 'success');
                        
                        if (feedbackDiv) {
                            feedbackDiv.innerHTML = `<span class="text-neon-green">✓ Request submitted! Ticket ID: <strong class="font-mono">${ticketId}</strong><br>Check your email for confirmation.</span>`;
                            feedbackDiv.className = 'mt-4 text-center text-sm text-neon-green';
                        }
                    } else if (result.localStorage) {
                        // Emails failed but form was stored locally
                        this.showNotification('Request submitted locally! Ticket ID: ' + ticketId + ' (Email system not configured)', 'warning');
                        
                        if (feedbackDiv) {
                            feedbackDiv.innerHTML = `<span class="text-warning-red">⚠ Request submitted locally (email system offline). Ticket ID: <strong class="font-mono">${ticketId}</strong><br>Save this ID for reference. <a href="install-phpmailer.php" target="_blank" class="underline">Install PHPMailer</a></span>`;
                            feedbackDiv.className = 'mt-4 text-center text-sm text-warning-red';
                        }
                    } else {
                        // Generic success
                        this.showNotification('Request submitted successfully! Ticket ID: ' + ticketId, 'success');
                        
                        if (feedbackDiv) {
                            feedbackDiv.innerHTML = `<span class="text-neon-green">✓ Request submitted! Ticket ID: <strong class="font-mono">${ticketId}</strong></span>`;
                            feedbackDiv.className = 'mt-4 text-center text-sm text-neon-green';
                        }
                    }
                    
                    // Reset form
                    form.reset();
                    requiredFields.forEach(field => {
                        field.style.borderColor = '#888888';
                    });
                } else {
                    // Server returned an error
                    this.showNotification('Error: ' + (result.error || 'Unknown error occurred'), 'error');
                }
                
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            })
            .catch(error => {
                console.error('Form submission error:', error);
                
                // Check error type and provide appropriate fallback
                let errorMessage = error.message;
                let isServerError = false;
                
                if (errorMessage.includes('403') || errorMessage.includes('Forbidden')) {
                    errorMessage = 'Server access forbidden. The PHP file may not be uploaded or has incorrect permissions.';
                    isServerError = true;
                } else if (errorMessage.includes('404') || errorMessage.includes('Not Found')) {
                    errorMessage = 'PHP file not found on server. Please upload submit-form.php to your hosting.';
                    isServerError = true;
                } else if (errorMessage.includes('Failed to fetch') || errorMessage.includes('NetworkError') || 
                           errorMessage.includes('TypeError') || error.name === 'TypeError') {
                    errorMessage = 'Cannot connect to server. The PHP backend may not be available.';
                    isServerError = true;
                }
                
                if (isServerError) {
                    // Fallback to local storage simulation
                    const ticketId = 'DSH-' + new Date().getFullYear() + '-' + Math.random().toString(36).substr(2, 6).toUpperCase();
                    
                    // Store locally
                    localStorage.setItem('lastTicketId', ticketId);
                    localStorage.setItem('lastSubmission', JSON.stringify({
                        ...formPayload,
                        ticketId,
                        timestamp: new Date().toISOString(),
                        status: 'open'
                    }));

                    // Show success message with note about backend
                    this.showNotification('Request submitted locally! Ticket ID: ' + ticketId + ' (Server error: ' + errorMessage + ')', 'warning');
                    
                    if (feedbackDiv) {
                        feedbackDiv.innerHTML = `<span class="text-warning-red">⚠ Request submitted locally (server error). Ticket ID: <strong class="font-mono">${ticketId}</strong><br>Save this ID for reference. <a href="install-phpmailer.php" target="_blank" class="underline">Check PHP installation</a></span>`;
                        feedbackDiv.className = 'mt-4 text-center text-sm text-warning-red';
                    }
                    
                    // Reset form
                    form.reset();
                    requiredFields.forEach(field => {
                        field.style.borderColor = '#888888';
                    });
                } else {
                    // Other errors
                    this.showNotification('Error submitting form: ' + errorMessage, 'error');
                }
                
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
            
            return;
        }

        // Fallback for other forms: Simulate form submission locally
        const ticketId = 'DSH-' + new Date().getFullYear() + '-' + Math.random().toString(36).substr(2, 6).toUpperCase();
        
        setTimeout(() => {
            // Store in localStorage
            localStorage.setItem('lastTicketId', ticketId);
            localStorage.setItem('lastSubmission', JSON.stringify({
                ...data,
                ticketId,
                timestamp: new Date().toISOString(),
                status: 'open'
            }));

            // Show success message
            this.showNotification('Request submitted successfully! Ticket ID: ' + ticketId, 'success');
            
            if (feedbackDiv) {
                feedbackDiv.innerHTML = `<span class="text-neon-green">✓ Request submitted! Ticket ID: <strong class="font-mono">${ticketId}</strong></span>`;
                feedbackDiv.className = 'mt-4 text-center text-sm text-neon-green';
            }
            
            // Reset form
            form.reset();
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;

            // Reset to first step for multi-step form
            if (type === 'service') {
                this.showStep(1);
            }
        }, 1500);
    }

    // Navigation
    setupNavigation() {
        const mobileMenuBtn = document.getElementById('mobile-menu');
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                this.toggleMobileMenu();
            });
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // Toggle Mobile Menu
    toggleMobileMenu() {
        let mobileNav = document.getElementById('mobile-nav');
        
        // Toggle visibility using CSS class
        if (mobileNav) {
            mobileNav.classList.toggle('active');
        }
    }

    // Counter Animations
    setupCounters() {
        const counters = [
            { id: 'stat-clients', target: 1247, suffix: '' },
            { id: 'stat-success', target: 96.7, suffix: '%' },
            { id: 'stat-response', target: 15, suffix: '' },
            { id: 'counter-experience', target: 15, suffix: '' },
            { id: 'counter-clients', target: 1247, suffix: '' },
            { id: 'counter-projects', target: 2156, suffix: '' },
            { id: 'counter-success', target: 96.7, suffix: '%' }
        ];

        counters.forEach(counter => {
            const element = document.getElementById(counter.id);
            if (element) {
                this.animateCounter(element, counter.target, counter.suffix);
            }
        });
    }

    animateCounter(element, target, suffix) {
        let current = 0;
        const increment = target / 100;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current) + suffix;
        }, 50);
    }

    // Pricing Calculator
    setupPricingCalculator() {
        const calcService = document.getElementById('calc-service');
        const calcComplexity = document.getElementById('calc-complexity');
        const calcTimeline = document.getElementById('calc-timeline');
        const getQuoteBtn = document.getElementById('get-quote');

        if (!calcService) return;

        const updatePrice = () => {
            const basePrice = parseInt(calcService.value);
            const complexityMultiplier = parseFloat(calcComplexity.value);
            const timelineMultiplier = parseFloat(calcTimeline.value);

            // Calculate additional features
            let featuresPrice = 0;
            document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
                featuresPrice += parseInt(checkbox.value);
            });

            const totalPrice = Math.round((basePrice * complexityMultiplier * timelineMultiplier) + featuresPrice);

            // Update display
            document.getElementById('base-price').textContent = `$${basePrice}`;
            document.getElementById('complexity-multiplier').textContent = `${complexityMultiplier}x`;
            document.getElementById('timeline-multiplier').textContent = `${timelineMultiplier}x`;
            document.getElementById('features-price').textContent = `$${featuresPrice}`;
            document.getElementById('total-price').textContent = `$${totalPrice}`;
        };

        calcService.addEventListener('change', updatePrice);
        calcComplexity.addEventListener('change', updatePrice);
        calcTimeline.addEventListener('change', updatePrice);
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', updatePrice);
        });

        if (getQuoteBtn) {
            getQuoteBtn.addEventListener('click', () => {
                const totalPrice = document.getElementById('total-price').textContent;
                this.showNotification(`Quote generated: ${totalPrice}. Redirecting to contact form...`, 'success');
                setTimeout(() => {
                    window.location.href = 'contact.html';
                }, 2000);
            });
        }

        // Initial calculation
        updatePrice();
    }

    // Team Member Modals
    setupTeamModals() {
        const teamMembers = {
            cipher: {
                name: 'Cipher',
                title: 'Lead Security Architect',
                bio: 'With over 15 years in cybersecurity, Cipher brings unparalleled expertise in penetration testing and network security. Former military intelligence officer with extensive experience in both offensive and defensive security operations.',
                specialties: ['Penetration Testing', 'Network Security', 'Cryptography', 'Risk Assessment'],
                achievements: ['Certified Ethical Hacker (CEH)', 'Offensive Security Certified Professional (OSCP)', 'CISSP Certified', 'Former NSA Contractor']
            },
            ghost: {
                name: 'Ghost',
                title: 'Digital Forensics Expert',
                bio: 'Digital forensics specialist with 12+ years in incident response and blockchain analysis. Expert in cryptocurrency recovery and theft investigation. Has recovered over $50M in stolen digital assets.',
                specialties: ['Blockchain Analysis', 'Digital Forensics', 'Cryptocurrency Recovery', 'Incident Response'],
                achievements: ['Certified Computer Forensics Examiner', 'Blockchain Expert Witness', 'FBI Cybercrime Consultant', 'Published Research Author']
            },
            phantom: {
                name: 'Phantom',
                title: 'Social Engineering Specialist',
                bio: 'Master of human factor security with 10+ years in psychological operations and social engineering. Specializes in account recovery and phone system exploitation with a 99% success rate.',
                specialties: ['Social Engineering', 'Account Recovery', 'Phone Systems', 'Psychological Operations'],
                achievements: ['Social Engineering Expert', 'Phone Phreaking Specialist', 'Corporate Security Trainer', 'Defcon Speaker']
            },
            shadow: {
                name: 'Shadow',
                title: 'Malware Specialist',
                bio: 'Expert malware developer and analyst with 8+ years creating undetectable solutions for legitimate security testing. Specializes in custom exploit development and reverse engineering.',
                specialties: ['Malware Development', 'Reverse Engineering', 'Exploit Development', 'Antivirus Evasion'],
                achievements: ['Malware Analysis Expert', 'Zero-Day Researcher', 'Security Conference Speaker', 'Published Author']
            },
            zero: {
                name: 'Zero',
                title: 'Web Application Expert',
                bio: 'Web application security specialist with 9+ years in advanced web exploitation. Expert in SQL injection, XSS, and database hacking techniques with a 97% success rate.',
                specialties: ['Web Exploitation', 'Database Hacking', 'API Security', 'Web Application Testing'],
                achievements: ['Web Application Security Expert', 'Bug Bounty Champion', 'OWASP Contributor', 'Security Researcher']
            },
            neuro: {
                name: 'Neuro',
                title: 'AI & ML Security Specialist',
                bio: 'AI/ML security expert with 7+ years in adversarial machine learning and AI system manipulation. Specializes in testing AI security and developing countermeasures.',
                specialties: ['AI Security', 'Machine Learning', 'Data Science', 'Adversarial AI'],
                achievements: ['AI Security Researcher', 'Machine Learning Expert', 'Published Academic Papers', 'Conference Speaker']
            }
        };

        document.querySelectorAll('[data-member]').forEach(card => {
            card.addEventListener('click', () => {
                const memberId = card.dataset.member;
                const member = teamMembers[memberId];
                
                if (member) {
                    this.showMemberModal(member);
                }
            });
        });

        const modal = document.getElementById('member-modal');
        const modalClose = document.getElementById('modal-close');

        if (modalClose) {
            modalClose.addEventListener('click', () => {
                modal.classList.remove('active');
            });
        }

        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        }
    }

    showMemberModal(member) {
        const modal = document.getElementById('member-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalContent = document.getElementById('modal-content');

        modalTitle.textContent = member.name;
        modalContent.innerHTML = `
            <div class="text-center mb-6">
                <div class="team-avatar mx-auto mb-4">${member.name.charAt(0)}</div>
                <h3 class="text-xl font-bold text-cyber-blue">${member.title}</h3>
            </div>
            <div class="space-y-4">
                <div>
                    <h4 class="font-bold text-neon-green mb-2">About</h4>
                    <p class="text-text-muted">${member.bio}</p>
                </div>
                <div>
                    <h4 class="font-bold text-neon-green mb-2">Specialties</h4>
                    <div class="flex flex-wrap gap-2">
                        ${member.specialties.map(specialty => 
                            `<span class="bg-dark-primary text-neon-green px-3 py-1 rounded-full text-sm">${specialty}</span>`
                        ).join('')}
                    </div>
                </div>
                <div>
                    <h4 class="font-bold text-neon-green mb-2">Achievements</h4>
                    <ul class="text-text-muted space-y-1">
                        ${member.achievements.map(achievement => 
                            `<li>• ${achievement}</li>`
                        ).join('')}
                    </ul>
                </div>
            </div>
        `;

        modal.classList.add('active');
    }

    // FAQ Functionality
    setupFAQ() {
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const faqId = question.dataset.faq;
                const answer = document.getElementById(`faq-${faqId}`);
                const icon = question.querySelector('span');

                if (answer.classList.contains('active')) {
                    answer.classList.remove('active');
                    icon.textContent = '+';
                } else {
                    // Close all other FAQs
                    document.querySelectorAll('.faq-answer').forEach(ans => {
                        ans.classList.remove('active');
                    });
                    document.querySelectorAll('.faq-question span').forEach(ic => {
                        ic.textContent = '+';
                    });

                    // Open clicked FAQ
                    answer.classList.add('active');
                    icon.textContent = '−';
                }
            });
        });
    }

    // Ticket Tracker
    setupTicketTracker() {
        const checkTicketBtn = document.getElementById('check-ticket');
        const ticketIdInput = document.getElementById('ticket-id');
        const ticketResult = document.getElementById('ticket-result');

        if (!checkTicketBtn) return;

        checkTicketBtn.addEventListener('click', () => {
            const ticketId = ticketIdInput.value.trim();
            if (!ticketId) {
                this.showNotification('Please enter a ticket ID', 'error');
                return;
            }

            // Simulate ticket lookup
            const mockTickets = {
                'DSH-2024-ABC123': {
                    status: 'progress',
                    service: 'Phone Access',
                    priority: 'Urgent',
                    update: 'In progress - 60% complete',
                    progress: 60
                },
                'DSH-2024-DEF456': {
                    status: 'open',
                    service: 'Bitcoin Recovery',
                    priority: 'Emergency',
                    update: 'Initial assessment completed',
                    progress: 20
                },
                'DSH-2024-GHI789': {
                    status: 'closed',
                    service: 'Social Media',
                    priority: 'Standard',
                    update: 'Completed successfully',
                    progress: 100
                }
            };

            const ticket = mockTickets[ticketId] || {
                status: 'open',
                service: 'Unknown Service',
                priority: 'Standard',
                update: 'Ticket not found in system',
                progress: 0
            };

            this.displayTicketResult(ticketId, ticket);
        });
    }

    displayTicketResult(ticketId, ticket) {
        const ticketResult = document.getElementById('ticket-result');
        const resultTicketId = document.getElementById('result-ticket-id');
        const resultStatus = document.getElementById('result-status');
        const resultService = document.getElementById('result-service');
        const resultPriority = document.getElementById('result-priority');
        const resultUpdate = document.getElementById('result-update');
        const resultProgress = document.getElementById('result-progress');

        resultTicketId.textContent = ticketId;
        resultService.textContent = ticket.service;
        resultPriority.textContent = ticket.priority;
        resultUpdate.textContent = ticket.update;
        resultProgress.style.width = ticket.progress + '%';

        // Set status class and text
        resultStatus.className = 'ticket-status';
        resultStatus.classList.add(`status-${ticket.status}`);
        resultStatus.textContent = ticket.status.charAt(0).toUpperCase() + ticket.status.slice(1);

        ticketResult.classList.remove('hidden');
        ticketResult.classList.add('visible');
    }

    // Testimonial Slider
    setupTestimonialSlider() {
        const slider = document.getElementById('testimonial-slider');
        if (slider) {
            new Splide(slider, {
                type: 'loop',
                autoplay: true,
                interval: 5000,
                pauseOnHover: true,
                arrows: false,
                pagination: true,
                gap: '2rem'
            }).mount();
        }
    }

    // Page-specific features
    initializePageSpecificFeatures() {
        const currentPage = window.location.pathname.split('/').pop() || 'index.html';

        switch (currentPage) {
            case 'index.html':
            case '':
                this.initializeHomePage();
                break;
            case 'services.html':
                this.initializeServicesPage();
                break;
            case 'about.html':
                this.initializeAboutPage();
                break;
            case 'contact.html':
                this.initializeContactPage();
                break;
        }
    }

    initializeHomePage() {
        // Get started button
        const getStartedBtn = document.getElementById('get-started');
        if (getStartedBtn) {
            getStartedBtn.addEventListener('click', () => {
                document.getElementById('service-form').scrollIntoView({ behavior: 'smooth' });
            });
        }

        // Learn more button
        const learnMoreBtn = document.getElementById('learn-more');
        if (learnMoreBtn) {
            learnMoreBtn.addEventListener('click', () => {
                window.location.href = 'services.html';
            });
        }
    }

    initializeServicesPage() {
        // Animate progress bars
        setTimeout(() => {
            document.querySelectorAll('.progress-fill').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        }, 500);
    }

    initializeAboutPage() {
        // Animate skill bars when they come into view
        const skillObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const skillFill = entry.target.querySelector('.skill-fill');
                    if (skillFill) {
                        const width = skillFill.style.width;
                        skillFill.style.width = '0%';
                        setTimeout(() => {
                            skillFill.style.width = width;
                        }, 100);
                    }
                }
            });
        });

        document.querySelectorAll('.skill-bar').forEach(bar => {
            skillObserver.observe(bar.parentElement);
        });
    }

    initializeContactPage() {
        // Additional contact page specific functionality
        const form = document.getElementById('contact-form');
        if (form) {
            // Real-time validation
            form.querySelectorAll('input, select, textarea').forEach(field => {
                field.addEventListener('blur', () => {
                    this.validateField(field);
                });
            });
        }
    }

    validateField(field) {
        const value = field.value.trim();
        let isValid = true;

        if (field.hasAttribute('required') && !value) {
            isValid = false;
        }

        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            isValid = emailRegex.test(value);
        }

        field.style.borderColor = isValid ? '#00ff41' : '#ff073a';
        return isValid;
    }

    // Language Translator Implementation (Local Translation)
    setupLanguageTranslator() {
        // Create language selector HTML
        this.createLanguageSelector();
        
        // Load translations
        this.loadTranslations();
        
        // Load saved language preference
        this.loadSavedLanguage();
        
        // Add responsive behavior
        this.setupLanguageSelectorResponsive();
    }

    async loadTranslations() {
        // Load English translations first (default)
        try {
            const response = await fetch('/translations/en.json');
            if (!response.ok) {
                console.error('Failed to load English translations, status:', response.status);
                // Try relative path
                const response2 = await fetch('translations/en.json');
                if (response2.ok) {
                    this.translations['en'] = await response2.json();
                    console.log('English translations loaded from relative path');
                } else {
                    console.error('Failed to load English translations from both paths');
                }
            } else {
                this.translations['en'] = await response.json();
                console.log('English translations loaded successfully');
            }
        } catch (error) {
            console.error('Failed to load English translations:', error);
        }
    }

    async loadLanguageFile(langCode) {
        if (this.translations[langCode]) {
            console.log('Using cached translations for', langCode);
            return this.translations[langCode];
        }

        try {
            // Try absolute path first
            let response = await fetch(`/translations/${langCode}.json`);
            if (!response.ok) {
                console.log('Trying relative path for', langCode);
                // Try relative path
                response = await fetch(`translations/${langCode}.json`);
            }
            
            if (response.ok) {
                this.translations[langCode] = await response.json();
                console.log('Loaded translations for', langCode);
                return this.translations[langCode];
            } else {
                console.error(`Failed to load ${langCode} translations, status:`, response.status);
                return this.translations['en']; // Fallback to English
            }
        } catch (error) {
            console.error(`Failed to load ${langCode} translations:`, error);
            return this.translations['en']; // Fallback to English
        }
    }

    createLanguageSelector() {
        // Check if selector already exists
        if (document.getElementById('language-selector')) {
            console.log('Language selector already exists');
            return;
        }

        console.log('Creating language selector...');

        const languages = {
            'en': { name: 'English', flag: '🇺🇸' },
            'de': { name: 'German', flag: '🇩🇪' },
            'pt': { name: 'Portuguese', flag: '🇵🇹' },
            'it': { name: 'Italian', flag: '🇮🇹' },
            'es': { name: 'Spanish', flag: '🇪🇸' },
            'fr': { name: 'French', flag: '🇫🇷' }
        };

        const languageSelector = document.createElement('div');
        languageSelector.id = 'language-selector';
        languageSelector.innerHTML = `
            <div class="language-selector-container">
                <button class="language-current" aria-haspopup="true" aria-expanded="false">
                    <span class="language-flag">${languages['en'].flag}</span>
                    <span class="language-name">${languages['en'].name}</span>
                    <span class="language-arrow">▼</span>
                </button>
                <ul class="language-dropdown" role="menu">
                    ${Object.entries(languages).map(([code, lang]) => `
                        <li role="menuitem" data-lang="${code}">
                            <span class="language-flag">${lang.flag}</span>
                            <span class="language-name">${lang.name}</span>
                        </li>
                    `).join('')}
                </ul>
            </div>
        `;

        // Insert language selector in navigation
        const navContainer = document.querySelector('.nav-container');
        if (navContainer) {
            navContainer.appendChild(languageSelector);
            console.log('Language selector added to nav-container');
        } else {
            console.error('Nav container not found! Language selector not added.');
        }

        // Add event listeners
        this.setupLanguageSelectorEvents();
    }

    setupLanguageSelectorEvents() {
        const currentButton = document.querySelector('.language-current');
        const dropdown = document.querySelector('.language-dropdown');
        
        if (!currentButton || !dropdown) {
            console.error('Language selector elements not found');
            return;
        }
        
        const menuItems = dropdown.querySelectorAll('li');

        // Toggle dropdown
        currentButton.addEventListener('click', (e) => {
            e.stopPropagation();
            const isExpanded = currentButton.getAttribute('aria-expanded') === 'true';
            currentButton.setAttribute('aria-expanded', !isExpanded);
            dropdown.classList.toggle('active');
        });

        // Handle language selection
        menuItems.forEach(item => {
            item.addEventListener('click', (e) => {
                const langCode = item.getAttribute('data-lang');
                console.log('Language selected:', langCode);
                this.changeLanguage(langCode);
                currentButton.setAttribute('aria-expanded', 'false');
                dropdown.classList.remove('active');
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', () => {
            currentButton.setAttribute('aria-expanded', 'false');
            dropdown.classList.remove('active');
        });
    }

    initializeGoogleTranslate() {
        // Removed: Google Translate integration replaced with local translation system
    }

    async changeLanguage(langCode) {
        console.log('changeLanguage called with:', langCode);
        
        // Load translation file for the selected language
        const translations = await this.loadLanguageFile(langCode);
        
        if (!translations) {
            console.error('Translations not available for', langCode);
            return;
        }

        console.log('Translations loaded for', langCode);

        // Update current language
        this.currentLanguage = langCode;

        // Apply translations to the page
        this.applyTranslations(translations);
        
        // Update UI
        this.updateLanguageUI(langCode);
        
        // Save preference
        this.saveLanguagePreference(langCode);
        
        console.log('Language changed to', langCode, 'successfully');
    }

    applyTranslations(translations) {
        console.log('Applying translations...');
        
        // Translation mapping: data attribute to translation key
        const elements = document.querySelectorAll('[data-translate]');
        console.log('Found', elements.length, 'elements with data-translate attribute');
        
        elements.forEach(element => {
            const key = element.getAttribute('data-translate');
            const translation = this.getNestedTranslation(translations, key);
            
            if (translation) {
                // Check if element is an input with placeholder
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    if (element.hasAttribute('placeholder')) {
                        element.placeholder = translation;
                    }
                } else {
                    // Regular text content
                    element.textContent = translation;
                }
            }
        });

        // Also translate navigation links by text matching
        this.translateNavigation(translations);
        
        // Auto-translate common button text
        this.autoTranslateButtons(translations);
        
        // Store current translations for page-wide use
        window.currentTranslations = translations;
        
        console.log('Translations applied successfully');
    }
    
    autoTranslateButtons(translations) {
        // Translate common button texts
        const buttons = document.querySelectorAll('button, .btn');
        buttons.forEach(button => {
            const text = button.textContent.trim().toUpperCase();
            
            // Get Started button
            if (text === 'GET STARTED' || text === translations.hero?.getStarted?.toUpperCase()) {
                if (translations.hero?.getStarted) {
                    button.textContent = translations.hero.getStarted;
                }
            }
            // Learn More button
            else if (text === 'LEARN MORE' || text === translations.hero?.learnMore?.toUpperCase()) {
                if (translations.hero?.learnMore) {
                    button.textContent = translations.hero.learnMore;
                }
            }
            // Submit button
            else if (text === 'SUBMIT REQUEST' || text === translations.contact?.submit?.toUpperCase()) {
                if (translations.contact?.submit) {
                    button.textContent = translations.contact.submit;
                }
            }
            // Check Status button
            else if (text === 'CHECK STATUS' || text === translations.ticketTracker?.check?.toUpperCase()) {
                if (translations.ticketTracker?.check) {
                    button.textContent = translations.ticketTracker.check;
                }
            }
        });
    }

    translateNavigation(translations) {
        // Translate navigation links
        const navLinks = document.querySelectorAll('.nav-links a, .mobile-nav-links a');
        navLinks.forEach(link => {
            const text = link.textContent.trim();
            if (text === 'Home' || text === translations.nav.home) {
                link.textContent = translations.nav.home;
            } else if (text === 'Services' || text.includes('Servic') || text.includes('Dienst')) {
                link.textContent = translations.nav.services;
            } else if (text === 'About' || text.includes('About') || text.includes('Über') || text.includes('Acerca') || text.includes('Propos') || text.includes('Chi')) {
                link.textContent = translations.nav.about;
            } else if (text === 'Contact' || text.includes('Contact') || text.includes('Kontakt')) {
                link.textContent = translations.nav.contact;
            }
        });
    }

    getNestedTranslation(obj, path) {
        // Support nested keys like "nav.home"
        return path.split('.').reduce((current, key) => {
            return current ? current[key] : undefined;
        }, obj);
    }

    updateLanguageUI(langCode) {
        const languages = {
            'en': { name: 'English', flag: '🇺🇸' },
            'de': { name: 'German', flag: '🇩🇪' },
            'pt': { name: 'Portuguese', flag: '🇵🇹' },
            'it': { name: 'Italian', flag: '🇮🇹' },
            'es': { name: 'Spanish', flag: '🇪🇸' },
            'fr': { name: 'French', flag: '🇫🇷' }
        };

        const lang = languages[langCode];
        if (lang) {
            const currentButton = document.querySelector('.language-current');
            currentButton.querySelector('.language-flag').textContent = lang.flag;
            currentButton.querySelector('.language-name').textContent = lang.name;
            
            // Update page language attribute
            document.documentElement.lang = langCode;
        }
    }

    saveLanguagePreference(langCode) {
        // Save to sessionStorage for current session
        sessionStorage.setItem('preferred-language', langCode);
        
        // Save to localStorage for persistence across sessions
        localStorage.setItem('preferred-language', langCode);
    }

    loadSavedLanguage() {
        // Load from sessionStorage first (prefers current session)
        let savedLang = sessionStorage.getItem('preferred-language');
        
        // If not in session, check localStorage
        if (!savedLang) {
            savedLang = localStorage.getItem('preferred-language');
        }

        // Apply saved language if different from default English
        if (savedLang && savedLang !== 'en') {
            // Wait for DOM to be fully loaded
            setTimeout(() => {
                this.changeLanguage(savedLang);
            }, 500); // Shorter delay since we don't need to wait for Google Translate
        }
    }

    setupLanguageSelectorResponsive() {
        // Handle responsive behavior for language selector
        const updateResponsive = () => {
            const selector = document.getElementById('language-selector');
            const currentButton = document.querySelector('.language-current');
            
            if (window.innerWidth <= 768) {
                // Mobile styles
                if (currentButton) {
                    currentButton.style.fontSize = '12px';
                    currentButton.style.padding = '6px 10px';
                }
            } else {
                // Desktop styles
                if (currentButton) {
                    currentButton.style.fontSize = '';
                    currentButton.style.padding = '';
                }
            }
        };

        // Initial update
        updateResponsive();
        
        // Update on resize
        window.addEventListener('resize', updateResponsive);
    }

    // Utility Functions
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        // Add inline styles for notification
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 1000;
            transition: all 0.3s ease;
            transform: translateX(100%);
            font-weight: bold;
            max-width: 300px;
        `;
        
        const colors = {
            success: 'background-color: #00ff41; color: #0a0a0a;',
            error: 'background-color: #ff073a; color: #ffffff;',
            info: 'background-color: #00d4ff; color: #0a0a0a;',
            warning: 'background-color: #ffc107; color: #0a0a0a;'
        };

        notification.style.cssText += colors[type];
        notification.textContent = message;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Animate out and remove
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new DarkSideHackers();
});

// Handle page visibility changes for performance
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        // Pause animations when page is not visible
        document.querySelectorAll('.card-hover').forEach(el => {
            el.style.animationPlayState = 'paused';
        });
    } else {
        // Resume animations when page becomes visible
        document.querySelectorAll('.card-hover').forEach(el => {
            el.style.animationPlayState = 'running';
        });
    }
});

// Error handling
window.addEventListener('error', (e) => {
    console.error('JavaScript Error:', e.error);
    // Optionally show user-friendly error message
});

// Handle unhandled promise rejections
window.addEventListener('unhandledrejection', (e) => {
    console.error('Unhandled Promise Rejection:', e.reason);
    e.preventDefault();
});
