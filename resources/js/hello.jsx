import React, { useState } from 'react';

const QuoteGenerator = () => {
  const quotes = [
    "The only limit to our realization of tomorrow is our doubts of today.",
    "Life is 10% what happens to us and 90% how we react to it.",
    "Success is not the key to happiness. Happiness is the key to success.",
    "The purpose of life is not to be happy. It is to be useful, to be honorable, to be compassionate, to have it make some difference that you have lived and lived well.",
    "The best way to predict your future is to create it."
  ];

  const [quote, setQuote] = useState(quotes[0]);

  const getRandomQuote = () => {
    const randomIndex = Math.floor(Math.random() * quotes.length);
    setQuote(quotes[randomIndex]);
  };

  return (
    <div style={{ textAlign: 'center', marginTop: '50px' }}>
      <h1>Random Quote Generator</h1>
      <p style={{ fontSize: '20px', fontStyle: 'italic' }}>{quote}</p>
      <button 
        onClick={getRandomQuote}
        style={{ padding: '10px 20px', fontSize: '16px', cursor: 'pointer' }}
      >
        Generate New Quote
      </button>
    </div>
  );
};

export default QuoteGenerator;
