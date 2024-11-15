import React from 'react';
//import logo from './logo.svg';
import logo from './images/shuriken-logo.png';
import { Helmet } from 'react-helmet';
import Calendar from './component/calendar/Calendar.tsx';
import './App.css';

function App() {
  const events = {
    '2024-11-12': 'Meeting',
    '2024-11-20': 'Birthday',
  };
  const formatDate = (date: Date) => {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
  };
  return (
    <div className="App">
      {/* <Helmet>
        <title>AIND - Art Is Not Dead</title>
      </Helmet>
      <header className="App-header">
        <img src={logo} className="App-logo" alt="logo" />
        <h1>Welcome to AIND</h1>
      </header>
      <body> */}
        <div className='calendarContainer'>
          {/* <Calendar /> */}
          <Calendar
            renderCell={(date) => (
              <div>
                <div>{date.getDate()}</div>
                {events[formatDate(date)] && (
                  <div style={{ color: 'blue' }}>
                    {events[formatDate(date)]}
                  </div>
                )}
              </div>
            )}
          />
        </div>
      {/* </body> */}
    </div>
  );
}

export default App;
