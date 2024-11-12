import React from 'react';
//import logo from './logo.svg';
import logo from './images/shuriken-logo.png';
import { Helmet } from 'react-helmet';
import Calendar from './component/calendar/Calendar.tsx';
import './App.css';

function App() {
  return (
    <div className="App">
      <Helmet>
        <title>AIND - Art Is Not Dead</title>
      </Helmet>
      <header className="App-header">
        <img src={logo} className="App-logo" alt="logo" />
        <h1>Welcome to AIND</h1>
      </header>
      <body className='mainContent'>
        <Calendar />
      </body>
    </div>
    
  );
}

export default App;
