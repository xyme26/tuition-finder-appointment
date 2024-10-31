<footer>
    <div class="web-links">
        <a href="about.php">About Us</a>
        <a href="help.php">FAQs</a>
        <a href="login_admin.php">Admin Login</a>
        <a href="#" id="needHelpLink" data-bs-toggle="modal" data-bs-target="#surveyModal">Need Help?</a>
    </div>
    <div class="feedback">
        <button id="feedbackBtn" data-bs-toggle="modal" data-bs-target="#feedbackModal">Give us feedback!</button>
    </div>
    <p>© <span id="currentYear"></span> Your Website. All rights reserved.</p>
</footer>

<!-- Survey Modal -->
<div class="modal fade" id="surveyModal" tabindex="-1" aria-labelledby="surveyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="surveyModalLabel">Find Your Perfect Tuition Center</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="progress mb-3" id="surveyProgressBar">
                    <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                
                <div id="helpQuestion">
                    <h2>Do you need help finding a tuition center?</h2>
                    <button class="btn btn-primary" onclick="startQuestions()">Yes, I need help</button>
                    <button class="btn btn-secondary" onclick="showNoHelpMessage()">No, I'm just browsing</button>
                </div>

                <div id="questionBox" style="display: none;">
                    <div id="question1">
                        <h3>What subject are you looking for?</h3>
                        <div class="subject-buttons">
                            <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Math')">Math</button>
                            <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Science')">Science</button>
                            <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'English')">English</button>
                            <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Biology')">Biology</button>
                            <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Chemistry')">Chemistry</button>
                            <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Physics')">Physics</button>
                            <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Add Math')">Add Math</button>
                            <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Account')">Account</button>
                            <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'History')">History</button>
                            <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Economy')">Economy</button>
                            <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Malay')">Bahasa Malaysia</button>
                        </div>
                    </div>

                    <div id="question2" style="display: none;">
                        <h3>What is your preferred city?</h3>
                        <input type="text" class="form-control" id="location" placeholder="Enter your city">
                    </div>

                    <div id="question3" style="display: none;">
                        <h3>What is your budget range for per subject?</h3>
                        <button class="btn btn-outline-primary" onclick="finishQuestions('RM0-20')">RM0 - RM20</button>
                        <button class="btn btn-outline-primary" onclick="finishQuestions('RM20 - RM40')">RM20 - RM40</button>
                        <button class="btn btn-outline-primary" onclick="finishQuestions('Above RM40')">Above RM40</button>
                    </div>

                    <div id="question4" style="display: none;">
                        <h3>What is your language preference?</h3>
                        <button class="btn btn-outline-primary" onclick="finishQuestions('Bahasa Malaysia')">Bahasa Malaysia</button>
                        <button class="btn btn-outline-primary" onclick="finishQuestions('English')">English</button>
                    </div>
                </div>

                <div id="noHelpMessage" style="display: none;">
                    <p>Okay. If you need help later, you can find assistance in the footer section.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="nextButton" onclick="nextQuestion()" style="display: none;">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- Feedback Popup -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">Website Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="feedbackForm">
                    <div class="mb-3">
                        <label class="form-label">How was your experience on our website?</label>
                        <div class="btn-group" role="group" aria-label="Feedback rating">
                            <input type="radio" class="btn-check" name="rating" id="ratingGood" value="good" autocomplete="off">
                            <label class="btn btn-outline-success" for="ratingGood">😃 Good</label>

                            <input type="radio" class="btn-check" name="rating" id="ratingNeutral" value="neutral" autocomplete="off">
                            <label class="btn btn-outline-warning" for="ratingNeutral">😐 Neutral</label>

                            <input type="radio" class="btn-check" name="rating" id="ratingBad" value="bad" autocomplete="off">
                            <label class="btn btn-outline-danger" for="ratingBad">😞 Bad</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="feedbackComment" class="form-label">Comments (Tell me why)</label>
                        <textarea class="form-control" id="feedbackComment" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submitFeedback">Submit Feedback</button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedSubjects = [];
let currentQuestion = 1;
const totalQuestions = 4;

function startQuestions() {
    document.getElementById('helpQuestion').style.display = 'none';
    document.getElementById('questionBox').style.display = 'block';
    document.getElementById('question1').style.display = 'block';
    document.getElementById('nextButton').style.display = 'inline-block';
    updateProgressBar(25);
}

function showNoHelpMessage() {
    document.getElementById('helpQuestion').style.display = 'none';
    document.getElementById('noHelpMessage').style.display = 'block';
    setTimeout(() => {
        $('#surveyModal').modal('hide');
    }, 3000);
}

function toggleSubject(button, subject) {
    button.classList.toggle('active');
    if (selectedSubjects.includes(subject)) {
        selectedSubjects = selectedSubjects.filter(s => s !== subject);
    } else {
        selectedSubjects.push(subject);
    }
}

function nextQuestion() {
    const currentQuestionElement = document.getElementById(`question${currentQuestion}`);
    currentQuestionElement.style.display = 'none';
    currentQuestion++;
    
    if (currentQuestion <= totalQuestions) {
        const nextQuestionElement = document.getElementById(`question${currentQuestion}`);
        nextQuestionElement.style.display = 'block';
        updateProgressBar(currentQuestion * 25);
        
        // Hide Next button on last question
        if (currentQuestion === totalQuestions) {
            document.getElementById('nextButton').style.display = 'none';
        }
    } else {
        finishQuestions();
    }
}

function finishQuestions(answer) {
    const location = document.getElementById('location').value;
    let budget = '';
    let language = '';

    if (currentQuestion === 3) {
        budget = answer === 'Below RM20' ? 'RM0-20' : answer;
    } else if (currentQuestion === 4) {
        language = answer;
    }

    let surveyAnswers = {
        subjects: selectedSubjects,
        location: location,
        budget: budget,
        language: language
    };

    console.log('Survey completed:', surveyAnswers);
    $('#surveyModal').modal('hide');
    updateRecommendations(surveyAnswers);
}

function updateProgressBar(percentage) {
    document.querySelector('.progress-bar').style.width = `${percentage}%`;
    document.querySelector('.progress-bar').setAttribute('aria-valuenow', percentage);
}

function resetSurvey() {
    currentQuestion = 1;
    document.getElementById('helpQuestion').style.display = 'block';
    document.getElementById('questionBox').style.display = 'none';
    document.getElementById('noHelpMessage').style.display = 'none';
    document.querySelectorAll('#questionBox > div').forEach(div => div.style.display = 'none');
    document.querySelector('.progress-bar').style.width = '0%';
    document.querySelectorAll('button.active').forEach(button => button.classList.remove('active'));
    selectedSubjects = [];
    document.getElementById('location').value = '';
    document.getElementById('nextButton').style.display = 'inline-block';
}

// Initialize the survey modal
document.addEventListener('DOMContentLoaded', function() {
    const surveyModal = new bootstrap.Modal(document.getElementById('surveyModal'));
    
    document.getElementById('needHelpLink').addEventListener('click', function(e) {
        e.preventDefault();
        resetSurvey();
        surveyModal.show();
    });

    $('#surveyModal').on('hidden.bs.modal', function () {
        resetSurvey();
    });
});


// Function to handle survey answers and update recommendations
function updateRecommendations(surveyAnswers) {
            console.log('Updating recommendations based on:', surveyAnswers);

            // Send an AJAX request to get recommendations
            fetch('get_recommendations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(surveyAnswers)
            })
            .then(response => response=>{
                if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
                return response.json();
            })
            .then(data => {
                console.log('Received recommendations:', data);

                // Get the recommendation grid element
                const recommendationGrid = document.getElementById('recommendation-grid');

                // Clear existing recommendations
                recommendationGrid.innerHTML = '';

                // Check if we received any recommendations
                if (data.length === 0) {
                    recommendationGrid.innerHTML = '<p>No matching tuition centers found.</p>';
                    return;
                }

                // Loop through the recommendations and create cards for each
                data.forEach(center => {
                    const centerCard = `
                        <div class="tuition-center-card">
                            <button class="favorite-btn" data-center-id="${center.id}" aria-label="Add to favorites">
                                <i class="fas fa-heart"></i>
                            </button>
                            <img src="${center.image}" alt="${center.name}">
                            <div class="card-content">
                                <h3>${center.name}</h3>
                                <p>Distance: ${center.distance ? center.distance.toFixed(1) + ' km' : 'N/A'}</p>
                                <p>Subjects: ${center.course_tags}</p>
                                <p>Price Range: ${center.price_range}</p>
                                <a href="tuition_details.php?id=${center.id}" class="btn btn-primary details-btn">Details</a>
                            </div>
                        </div>
                    `;
                    recommendationGrid.innerHTML += centerCard;
                });

                // Reinitialize favorite buttons if needed
                initializeFavoriteButtons();
            })
            .catch(error => {
                console.error('Error fetching recommendations:', error);
                document.getElementById('recommendation-grid').innerHTML = '<p>Error fetching recommendations. Please try again later.</p>';
            });
        }
</script>


