<template>
    <div id="devices_list" class="flex bg-white shadow-md p-1 rounded-md shadow-md flex-wrap">
      <span class="inline-flex items-center px-2 m-1 rounded-md text-xs font-medium bg-gray-300 text-gray-800 cursor-pointer">
        <div v-if="device[0]">Координаты устройства: {{device[0].id}} (X:{{device[0].x}}, Y:{{device[0].y}})</div>
        <div v-if="mouse_point">Координаты мыши: (X:{{mouse_point.x}}, Y:{{mouse_point.y}})</div>
      </span>
    <div id="app_canvas"  @mousemove="pmove" > <CanvasComponent :correct_coordinate=correct_coordinate /> </div>
  </div>
</template>

<script>
import CanvasComponent from "./components/CanvasComponent.vue";

export default {
  name: "App",
  components: {
    CanvasComponent
  },
  data() {
    return {         
      device: [],  
      data:[],
      mouse_point: {'x':0, 'y':0},   
      correct_coordinate: {'x': -75, 'y': -85},      
    };
  },
  mounted() {   

    setInterval(async() => {             
        let data = localStorage.getItem('data_ptp') ? JSON.parse(localStorage.getItem('data_ptp')) : default_data;  
        const {x,  y} = this.get_coordinate(data[0].x, data[0].y);
        data[0].x = x;
        data[0].y = y;
        this.device = data;
        //data.forEach(obj => {      
        //console.log(`${obj.id} ${obj.x} ${obj.y}`);    
        //console.log(`${obj.points}`);    
        // console.log('-------------------');
      //});
        
      }, 2000);

      const size_w = window.innerWidth;
      const size_h = window.innerHeight;
      console.log(size_h + ',' + size_w);
  },
  methods: {
    pmove(event) {      
      this.mouse_point.x = event.x;
      this.mouse_point.y = event.y;
    },

    get_data(){          
      data = localStorage.getItem('data_ptp') ? JSON.parse(localStorage.getItem('data_ptp')) : default_data;  
    },
    
    get_coordinate(x, y){
      const rx = (x*1 + this.correct_coordinate.x);
      const ry = (y*1 + this.correct_coordinate.y);
      return {'x':rx, 'y':ry};
    }
  }
};
</script>

<style>
* {
  padding: 0;
  margin: 0;
}
#app_canvas {
  font-family: "Avenir", Helvetica, Arial, sans-serif;  
  text-align: left;  
  overflow:scroll; 
  height:660px;
}
</style>;