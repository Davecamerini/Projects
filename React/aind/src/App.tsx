import React from 'react';
//import logo from './logo.svg';
import logo from './images/shuriken-logo.png';
import { Helmet } from 'react-helmet';
import './App.css';

function App() {
  return (
    <div className="App">
      <Helmet>
        <title>AIND</title>
      </Helmet>
      <header className="App-header">
        <img src={logo} className="App-logo" alt="logo" />
        <p>
          Edit <code>src/App.tsx</code> and save to fuck your mom.
        </p>
        <a
          className="App-link"
          href="https://reactjs.org"
          target="_blank"
          rel="noopener noreferrer"
        >
          Learn React
        </a>
      </header>
      <body className='mainContent'>
        <h1>Welcome to AIND</h1>
      </body>
    </div>
    
  );
}

export default App;
