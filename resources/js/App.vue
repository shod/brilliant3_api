<template>
  <div>Координаты устройства: {{point.name}} (X:{{point.x}}, Y:{{point.y}})</div>
</template>

<script>
export default {
  name: "App",
  data() {
    return {   
      data: [],
      point: [],   
      is_ticker_exists: false,      
      tickers: [],                
    };
  },

  methods: {
    
  },

  mounted(){
      this.point.name = '';
      this.point.x = 0;
      this.point.y = 0;
      setInterval(async() => {               
        const f = await fetch(base_url+'/api/data/ptps/'+deviceId);
        const res = await f.json();  
  
        //this.data = res.data;     

        if(res.data){          
          // res.data.forEach(obj => {    
          //   console.log(`${obj.id} ${obj.x} ${obj.y}`);    
          //   console.log('----- V --------------');
          // });
          localStorage.setItem('data_ptp', JSON.stringify(res.data));          
          res.data.forEach(obj => {            
            this.point.name = obj.id;
            this.point.x = obj.x;
            this.point.y = obj.y;      
          });
          res.data = ""
        }   
                    
      }, 3000);
    },
};
</script>