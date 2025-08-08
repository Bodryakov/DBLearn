// app.js: Обработка тестов и навигации
document.addEventListener('DOMContentLoaded', () => {
    // Обработка тестов
    const testItems = document.querySelectorAll('.test-item');
    
    testItems.forEach(testItem => {
        const answers = testItem.querySelectorAll('.answer');
        const correctIndex = parseInt(testItem.dataset.correctIndex);
        let answered = false;
        
        answers.forEach(answer => {
            const input = answer.querySelector('input');
            const label = answer.querySelector('label');
            
            input.addEventListener('change', () => {
                if (answered) return;
                
                const answerId = parseInt(answer.dataset.answerId);
                const isCorrect = correctIndex === answerId;
                
                // Блокировка ответов
                answered = true;
                testItem.querySelectorAll('input').forEach(inp => {
                    inp.disabled = true;
                });
                
                // Визуальная индикация
                if (isCorrect) {
                    answer.classList.add('correct');
                } else {
                    answer.classList.add('incorrect');
                    
                    // Показать правильный ответ
                    const correctAnswer = testItem.querySelector(`.answer[data-answer-id="${correctIndex}"]`);
                    correctAnswer.classList.add('correct');
                }
            });
        });
    });
    
    // Инициализация подсветки кода
    if (window.hljs) {
        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightElement(block);
        });
    }
});