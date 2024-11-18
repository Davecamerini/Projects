// eslint-disable-next-line
import logo from './logo.svg';
import './App.css';
import { infinity } from 'ldrs';

function App() {
  infinity.register();
  return (
    <div className="App">
      <header className="App-header">
        {/* <img src={logo} className="App-logo" alt="logo" /> */}
        <l-infinity
          size="250"
          stroke="15"
          stroke-length="0.1"
          bg-opacity="0.1"
          speed="1.5"
          color="white" 
        ></l-infinity>
        <p>
          Edit <code>src/App.tsx</code> and save to reload.
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
    </div>
  );
}

export default App;