<template>    	
  <div id="pixi">
    <canvas id="map-canvas"></canvas>
  </div>
</template>

<script>
import * as PIXI from "pixi.js";

export default {
  name: "CanvasComponent",  
  props: ['correct_coordinate'],
  data() {
    return {
      app: null,              
      device: [],
      data: [],
      gw: [],
      correct_x: this.correct_coordinate.x,
      correct_y: this.correct_coordinate.y,
      device_texture: null,
      background_map: null,
    };
  },
  async mounted() {
    this.background_map = await PIXI.Assets.load(map_image_path);
    this.device_texture = await PIXI.Assets.load(device_image_path);
    const newDevice = {
        id: deviceId, x:"1", y:"1", points:[]
    };
    this.data.push(newDevice);

    this.app = new PIXI.Application({      
      width: 1200,
      height: 602,      
      antialias: true,    
      transparent:true,  
      backgroundColor: '#FFFFFF',
      view: document.getElementById("map-canvas"),
    });
    this.$el.appendChild(this.app.view);
    this.app.renderer.view.style.display = "block";
    //this.app.renderer.autoResize = true;
    //this.app.renderer.resize(window.innerWidth, window.innerHeight);
    this.drawMap();   
    
    this.get_data(deviceId);
    this.draw_device();
    //this.gw_move();
    
    /**
     * Перемещение устройств
     */
    // setInterval(async() => {                           
    //   if(this.data){        
    //     this.data.forEach(element => {                      
    //       if(this.device[element.id]){    
    //         const {x,  y} = this.get_coordinate(element.x, element.y);                  
    //         this.device[element.id].x = x;
    //         this.device[element.id].y = y;                           
    //       }
    //     });
    //   }
    // }, 3000);
    
    /* Получение данных */
    setInterval(() => {             
      this.get_data(deviceId);
      this.gw_move();
      this.move_device();
      }, 6000);
    
  },

  methods: {    
    

    async get_data(deviceId){    
      const f = await fetch(base_url+'/api/data/ptps/'+deviceId);        
      const res = await f.json();    
      this.data = res.data;      
      localStorage.setItem('data_ptp', JSON.stringify(res.data));          
    }, 

    async drawMap() {      
                  
      const map = new PIXI.Sprite(this.background_map);
      map.zIndex = 0,      
      map.width = 1143
      map.height = 602
      this.app.stage.addChild(map);      
    },    

    async draw_device(){   
              
      if(this.data){
        
          this.data.forEach(element => {          
            
          // This creates a texture 
            this.device[element.id] = new PIXI.Sprite(this.device_texture);
            this.device[element.id].transparent = true,
            this.device[element.id].anchor.set(0.5);
            this.device[element.id].zIndex = 10;
            // this.device[element.id].x = (this.app.renderer.width / 2);
            // this.device[element.id].y = (this.app.renderer.height / 2);
            this.device[element.id].x = 500;
            this.device[element.id].y = 500;
            this.app.stage.addChild(this.device[element.id]);
        });

        this.draw_gw();
      }
    },

    move_device(){
      if(this.data){        
        this.data.forEach(element => {                      
          if(this.device[element.id]){    
            const {x,  y} = this.get_coordinate(element.x, element.y);                  
            this.device[element.id].x = x;
            this.device[element.id].y = y;                           
          }
        });
      }
    },

    add_text(text){
      // Создаем новый объект текста
      const pixi_text = new PIXI.Text(text, {
          fontFamily: 'Arial',
          fontSize: 36,
          fill: 0xff0000,
          align: 'center'
      });
      return pixi_text;
    },

    /** Рисуем хосты */
    draw_gw_one(index, x, y, radius, text){    
      console.log('draw_gw:' + x + ',' + y);     
      const colors = ['0xff00000', '0x00ff00', '0x626262'];
      
        this.destroy_gw(this.gw[index]);        

        this.gw[index] = new PIXI.Graphics();                      
        //this.app.stage.addChild(this.gw[index]);
        
        this.gw[index].beginFill(colors[index]);
        this.gw[index].drawCircle(0, 0, (20-index * 5));
        this.gw[index].endFill();     

        this.gw[index].lineStyle(3, colors[index])                
        this.gw[index].drawCircle(0, 0, radius);  
                
    },


    destroy_gw(gw_spite){
      // Удаляем спрайт со сцены
      this.app.stage.removeChild(gw_spite);

      // Уничтожаем спрайт
      gw_spite.destroy();
    },

    /** Рисуем хосты */
    draw_gw(){           
      const colors = ['0xff00000', '0x00ff00', '0x626262'];
      for (let index = 0; index < 3; index++) {
        this.gw[index] = new PIXI.Graphics();                      
        //this.app.stage.addChild(this.gw[index]);
        
        this.gw[index].beginFill(colors[index]);
        this.gw[index].drawCircle(0, 0, (20-index * 5));
        this.gw[index].endFill();             
        
      }
      
    },

    gw_move(){    
      console.log('gw_move');  
      if(this.data[0].points.length == 0){        
        return;
      }
      
      let gw_point = []; 
      for (let index = 0; index < 3; index++) {
        
        gw_point = this.data[0].points[index];              
        console.log(gw_point);
        const {x,  y} = this.get_coordinate(gw_point.x, gw_point.y);
        
        this.draw_gw_one(index, x, y, gw_point.radius)

        this.gw[index].x = x;        
        this.gw[index].y = y;     
        
        this.app.stage.addChild(this.gw[index]);
        //this.gw[index].drawCircle(100, 100, 10);
        //this.gw[index].radius = gw_point.radius * 10;      
      }
    },

    get_coordinate(x, y){
      const rx = (x*1 + this.correct_x);
      const ry = (y*1 + this.correct_y);
      return {'x':rx, 'y':ry};
    }
  }
};
</script>

<style>
#pixi {
    height: 100%;
    width: 100%;
}
</style>