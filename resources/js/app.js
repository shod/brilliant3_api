//import './bootstrap';
// app.js

import {createApp} from 'vue'

import App from './App.vue'
import * as PIXI from 'pixi.js';

createApp(App).mount("#app")

let app;
let player;


const Devices = ['3485182548CA'];
let device = [];

//const response_data = '[{"id": 1, "x": 300, "y": 530}, {"id": 3, "x": 300, "y": 400}]';
//var data = JSON.parse(response_data);
var data = [];

async function get_data(deviceId){
  console.info(deviceId);
  const f = await fetch(base_url+'/api/data/ptps/'+deviceId);        
  const res = await f.json();  
  
  data = res.data;  
}

get_data(deviceId);

window.onload = async function(){  
  app = new PIXI.Application(
    {
      width: 1143,
      height: 602,      
      backgroundColor: '#FFFFFF',      
    }
  );


  document.body.appendChild(app.view);

  const background_map = await PIXI.Assets.load(map_image_path);
  const map = new PIXI.Sprite(background_map);
  app.stage.addChild(map);

    // load the texture we need
  const device_texture = await PIXI.Assets.load(device_image_path);

  data.forEach(element => {
    console.log('element');
    console.log(element);
    // This creates a texture 
    device[element.id] = new PIXI.Sprite(device_texture);
    device[element.id].anchor.set(0.5);
    device[element.id].x = (app.renderer.width / 2);
    device[element.id].y = (app.renderer.height / 2);
    app.stage.addChild(device[element.id]);
  });
  

  console.log(data)
  
  // Listen for frame updates
  app.ticker.add(() => {    

    if(data){
      data.forEach(obj => {    
        console.log(`${obj.id} ${obj.x} ${obj.y}`);    
        console.log('-------------------');
      });
      data.forEach(obj => {
        device[obj.id].x = obj.x;
        device[obj.id].y = obj.y;      
      });
      data = ""
    }    
    //device[deviceId].x += 0.05;
  },1000);

    setInterval(async() => {              
      get_data(deviceId);
    }, 3000);
}

