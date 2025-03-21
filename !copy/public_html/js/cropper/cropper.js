/** 
 * Copyright (c) 2006-2009, David Spurr (http://www.defusion.org.uk/)
 * 
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 * 
 *     * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 *     * Neither the name of the David Spurr nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * http://www.opensource.org/licenses/bsd-license.php
 * 
 * See scriptaculous.js for full scriptaculous licence
 */

var CropDraggable=Class.create(Draggable,{
    initialize:function(a){
        this.options=Object.extend({
            drawMethod:function(){}
        },arguments[1]||{});
    this.element=$(a);
    this.handle=this.element;
    this.delta=this.currentDelta();
    this.dragging=false;
    this.eventMouseDown=this.initDrag.bindAsEventListener(this);
    Event.observe(this.handle,"mousedown",this.eventMouseDown);
    Draggables.register(this);
},
draw:function(a){
    var e=Element.cumulativeOffset(this.element),c=this.currentDelta();
    e[0]-=c[0];
    e[1]-=c[1];
    var b=[0,1].map(function(d){
        return(a[d]-e[d]-this.offset[d]);
    }.bind(this));
    this.options.drawMethod(b);
}
});
var Cropper={};

Cropper.Img=Class.create({
    initialize:function(c,a){
        this.options=Object.extend({
            ratioDim:{
                x:0,
                y:0
            },
            minWidth:0,
            minHeight:0,
            displayOnInit:false,
            onEndCrop:Prototype.emptyFunction,
            captureKeys:true,
            onloadCoords:null,
            maxWidth:0,
            maxHeight:0,
            autoIncludeCSS:true
        },a||{});
        this.img=$(c);
        this.clickCoords={
            x:0,
            y:0
        };

        this.dragging=false;
        this.resizing=false;
        this.isWebKit=/Konqueror|Safari|KHTML/.test(navigator.userAgent);
        this.isIE=/MSIE/.test(navigator.userAgent);
        this.isOpera8=/Opera\s[1-8]/.test(navigator.userAgent);
        this.ratioX=0;
        this.ratioY=0;
        this.attached=false;
        this.fixedWidth=(this.options.maxWidth>0&&(this.options.minWidth>=this.options.maxWidth));
        this.fixedHeight=(this.options.maxHeight>0&&(this.options.minHeight>=this.options.maxHeight));
        if(typeof this.img=="undefined"){
            return;
        }
        if(this.options.autoIncludeCSS){
            $$("script").each(function(e){
                if(e.src.match(/\/cropper([^\/]*)\.js/)){
                    var f=e.src.replace(/\/cropper([^\/]*)\.js.*/,""),d=document.createElement("link");
                    d.rel="stylesheet";
                    d.type="text/css";
                    d.href=f+"/cropper.css";
                    d.media="screen";
                    document.getElementsByTagName("head")[0].appendChild(d);
                }
            });
    }
    if(this.options.ratioDim.x>0&&this.options.ratioDim.y>0){
        var b=this.getGCD(this.options.ratioDim.x,this.options.ratioDim.y);
        this.ratioX=this.options.ratioDim.x/b;
        this.ratioY=this.options.ratioDim.y/b;
    }
    this.subInitialize();
    if(this.img.complete||this.isWebKit){
        this.onLoad();
    }else{
        Event.observe(this.img,"load",this.onLoad.bindAsEventListener(this));
    }
},
getGCD:function(d,c){
    if(c===0){
        return d;
    }
    return this.getGCD(c,d%c);
},
onLoad:function(){
    var c="imgCrop_";
    var e=this.img.parentNode;
    var b="";
    if(this.isOpera8){
        b=" opera8";
    }
    this.imgWrap=new Element("div",{
        "class":c+"wrap"+b
        });
    this.north=new Element("div",{
        "class":c+"overlay "+c+"north"
        }).insert(new Element("span"));
    this.east=new Element("div",{
        "class":c+"overlay "+c+"east"
        }).insert(new Element("span"));
    this.south=new Element("div",{
        "class":c+"overlay "+c+"south"
        }).insert(new Element("span"));
    this.west=new Element("div",{
        "class":c+"overlay "+c+"west"
        }).insert(new Element("span"));
    var d=[this.north,this.east,this.south,this.west];
    this.dragArea=new Element("div",{
        "class":c+"dragArea"
        });
    d.each(function(f){
        this.dragArea.insert(f);
    },this);
    this.handleN=new Element("div",{
        "class":c+"handle "+c+"handleN"
        });
    this.handleNE=new Element("div",{
        "class":c+"handle "+c+"handleNE"
        });
    this.handleE=new Element("div",{
        "class":c+"handle "+c+"handleE"
        });
    this.handleSE=new Element("div",{
        "class":c+"handle "+c+"handleSE"
        });
    this.handleS=new Element("div",{
        "class":c+"handle "+c+"handleS"
        });
    this.handleSW=new Element("div",{
        "class":c+"handle "+c+"handleSW"
        });
    this.handleW=new Element("div",{
        "class":c+"handle "+c+"handleW"
        });
    this.handleNW=new Element("div",{
        "class":c+"handle "+c+"handleNW"
        });
    this.selArea=new Element("div",{
        "class":c+"selArea"
        });
    [new Element("div",{
        "class":c+"marqueeHoriz "+c+"marqueeNorth"
        }).insert(new Element("span")),new Element("div",{
        "class":c+"marqueeVert "+c+"marqueeEast"
        }).insert(new Element("span")),new Element("div",{
        "class":c+"marqueeHoriz "+c+"marqueeSouth"
        }).insert(new Element("span")),new Element("div",{
        "class":c+"marqueeVert "+c+"marqueeWest"
        }).insert(new Element("span")),this.handleN,this.handleNE,this.handleE,this.handleSE,this.handleS,this.handleSW,this.handleW,this.handleNW,new Element("div",{
        "class":c+"clickArea"
        })].each(function(f){
        this.selArea.insert(f);
    },this);
    this.imgWrap.appendChild(this.img);
    this.imgWrap.appendChild(this.dragArea);
    this.dragArea.appendChild(this.selArea);
    this.dragArea.appendChild(new Element("div",{
        "class":c+"clickArea"
        }));
    e.appendChild(this.imgWrap);
    this.startDragBind=this.startDrag.bindAsEventListener(this);
    Event.observe(this.dragArea,"mousedown",this.startDragBind);
    this.onDragBind=this.onDrag.bindAsEventListener(this);
    Event.observe(document,"mousemove",this.onDragBind);
    this.endCropBind=this.endCrop.bindAsEventListener(this);
    Event.observe(document,"mouseup",this.endCropBind);
    this.resizeBind=this.startResize.bindAsEventListener(this);
    this.handles=[this.handleN,this.handleNE,this.handleE,this.handleSE,this.handleS,this.handleSW,this.handleW,this.handleNW];
    this.registerHandles(true);
    if(this.options.captureKeys){
        this.keysBind=this.handleKeys.bindAsEventListener(this);
        Event.observe(document,"keypress",this.keysBind);
    }
    var a=new CropDraggable(this.selArea,{
        drawMethod:this.moveArea.bindAsEventListener(this)
        });
    this.setParams();
},
registerHandles:function(b){
    for(var d=0;d<this.handles.length;d++){
        var g=$(this.handles[d]);
        if(b){
            var a=false;
            if(this.fixedWidth&&this.fixedHeight){
                a=true;
            }else{
                if(this.fixedWidth||this.fixedHeight){
                    var c=g.className.match(/([S|N][E|W])$/),f=g.className.match(/(E|W)$/),e=g.className.match(/(N|S)$/);
                    if(c||(this.fixedWidth&&f)||(this.fixedHeight&&e)){
                        a=true;
                    }
                }
            }
        if(a){
        g.hide();
    }else{
        Event.observe(g,"mousedown",this.resizeBind);
    }
    }else{
    g.show();
    Event.stopObserving(g,"mousedown",this.resizeBind);
}
}
},
setParams:function(){
    this.imgW=this.img.width;
    this.imgH=this.img.height;
    $(this.north).setStyle({
        height:0
    });
    $(this.east).setStyle({
        width:0,
        height:0
    });
    $(this.south).setStyle({
        height:0
    });
    $(this.west).setStyle({
        width:0,
        height:0
    });
    $(this.imgWrap).setStyle({
        width:this.imgW+"px",
        height:this.imgH+"px"
        });
    $(this.selArea).hide();
    var b={
        x1:0,
        y1:0,
        x2:0,
        y2:0
    },a=false;
    if(this.options.onloadCoords!==null){
        b=this.cloneCoords(this.options.onloadCoords);
        a=true;
    }else{
        if(this.options.ratioDim.x>0&&this.options.ratioDim.y>0){
            b.x1=Math.ceil((this.imgW-this.options.ratioDim.x)/2);
            b.y1=Math.ceil((this.imgH-this.options.ratioDim.y)/2);
            b.x2=b.x1+this.options.ratioDim.x;
            b.y2=b.y1+this.options.ratioDim.y;
            a=true;
        }
    }
    this.setAreaCoords(b,false,false,1);
if(this.options.displayOnInit&&a){
    this.selArea.show();
    this.drawArea();
    this.endCrop();
}
this.attached=true;
},
remove:function(){
    if(this.attached){
        this.attached=false;
        this.imgWrap.parentNode.insertBefore(this.img,this.imgWrap);
        this.imgWrap.parentNode.removeChild(this.imgWrap);
        Event.stopObserving(this.dragArea,"mousedown",this.startDragBind);
        Event.stopObserving(document,"mousemove",this.onDragBind);
        Event.stopObserving(document,"mouseup",this.endCropBind);
        this.registerHandles(false);
        if(this.options.captureKeys){
            Event.stopObserving(document,"keypress",this.keysBind);
        }
    }
},
reset:function(){
    if(!this.attached){
        this.onLoad();
    }else{
        this.setParams();
    }
    this.endCrop();
},
handleKeys:function(b){
    var a={
        x:0,
        y:0
    };

    if(!this.dragging){
        switch(b.keyCode){
            case (37):
                a.x=-1;
                break;
            case (38):
                a.y=-1;
                break;
            case (39):
                a.x=1;
                break;
            case (40):
                a.y=1;
                break;
        }
        if(a.x!==0||a.y!==0){
            if(b.shiftKey){
                a.x*=10;
                a.y*=10;
            }
            this.moveArea([this.areaCoords.x1+a.x,this.areaCoords.y1+a.y]);
            this.endCrop();
            Event.stop(b);
        }
    }
},
calcW:function(){
    return(this.areaCoords.x2-this.areaCoords.x1);
},
calcH:function(){
    return(this.areaCoords.y2-this.areaCoords.y1);
},
moveArea:function(a){
    this.setAreaCoords({
        x1:a[0],
        y1:a[1],
        x2:a[0]+this.calcW(),
        y2:a[1]+this.calcH()
        },true,false);
    this.drawArea();
},
cloneCoords:function(a){
    return{
        x1:a.x1,
        y1:a.y1,
        x2:a.x2,
        y2:a.y2
        };

},
setAreaCoords:function(i,a,m,h,k){
    if(a){
        var j=i.x2-i.x1,f=i.y2-i.y1;
        if(i.x1<0){
            i.x1=0;
            i.x2=j;
        }
        if(i.y1<0){
            i.y1=0;
            i.y2=f;
        }
        if(i.x2>this.imgW){
            i.x2=this.imgW;
            i.x1=this.imgW-j;
        }
        if(i.y2>this.imgH){
            i.y2=this.imgH;
            i.y1=this.imgH-f;
        }
    }else{
    if(i.x1<0){
        i.x1=0;
    }
    if(i.y1<0){
        i.y1=0;
    }
    if(i.x2>this.imgW){
        i.x2=this.imgW;
    }
    if(i.y2>this.imgH){
        i.y2=this.imgH;
    }
    if(h!==null){
        if(this.ratioX>0){
            this.applyRatio(i,{
                x:this.ratioX,
                y:this.ratioY
                },h,k);
        }else{
            if(m){
                this.applyRatio(i,{
                    x:1,
                    y:1
                },h,k);
            }
        }
        var b=[this.options.minWidth,this.options.minHeight],l=[this.options.maxWidth,this.options.maxHeight];
    if(b[0]>0||b[1]>0||l[0]>0||l[1]>0){
        var g={
            a1:i.x1,
            a2:i.x2
            },e={
            a1:i.y1,
            a2:i.y2
            },d={
            min:0,
            max:this.imgW
            },c={
            min:0,
            max:this.imgH
            };

        if((b[0]!==0||b[1]!==0)&&m){
            if(b[0]>0){
                b[1]=b[0];
            }else{
                if(b[1]>0){
                    b[0]=b[1];
                }
            }
        }
    if((l[0]!==0||l[0]!==0)&&m){
    if(l[0]>0&&l[0]<=l[1]){
        l[1]=l[0];
    }else{
        if(l[1]>0&&l[1]<=l[0]){
            l[0]=l[1];
        }
    }
}
if(b[0]>0){
    this.applyDimRestriction(g,b[0],h.x,d,"min");
}
if(b[1]>1){
    this.applyDimRestriction(e,b[1],h.y,c,"min");
}
if(l[0]>0){
    this.applyDimRestriction(g,l[0],h.x,d,"max");
}
if(l[1]>1){
    this.applyDimRestriction(e,l[1],h.y,c,"max");
}
i={
    x1:g.a1,
    y1:e.a1,
    x2:g.a2,
    y2:e.a2
    };

}
}
}
this.areaCoords=i;
},
applyDimRestriction:function(d,f,e,c,b){
    var a;
    if(b=="min"){
        a=((d.a2-d.a1)<f);
    }else{
        a=((d.a2-d.a1)>f);
    }
    if(a){
        if(e==1){
            d.a2=d.a1+f;
        }else{
            d.a1=d.a2-f;
        }
        if(d.a1<c.min){
            d.a1=c.min;
            d.a2=f;
        }else{
            if(d.a2>c.max){
                d.a1=c.max-f;
                d.a2=c.max;
            }
        }
    }
},
applyRatio:function(c,a,e,b){
    var d;
    if(b=="N"||b=="S"){
        d=this.applyRatioToAxis({
            a1:c.y1,
            b1:c.x1,
            a2:c.y2,
            b2:c.x2
            },{
            a:a.y,
            b:a.x
            },{
            a:e.y,
            b:e.x
            },{
            min:0,
            max:this.imgW
            });
        c.x1=d.b1;
        c.y1=d.a1;
        c.x2=d.b2;
        c.y2=d.a2;
    }else{
        d=this.applyRatioToAxis({
            a1:c.x1,
            b1:c.y1,
            a2:c.x2,
            b2:c.y2
            },{
            a:a.x,
            b:a.y
            },{
            a:e.x,
            b:e.y
            },{
            min:0,
            max:this.imgH
            });
        c.x1=d.a1;
        c.y1=d.b1;
        c.x2=d.a2;
        c.y2=d.b2;
    }
},
applyRatioToAxis:function(i,f,h,b){
    var j=Object.extend(i,{}),e=j.a2-j.a1,a=Math.floor(e*f.b/f.a),g=null,c=null,d=null;
    if(h.b==1){
        g=j.b1+a;
        if(g>b.max){
            g=b.max;
            d=g-j.b1;
        }
        j.b2=g;
    }else{
        g=j.b2-a;
        if(g<b.min){
            g=b.min;
            d=g+j.b2;
        }
        j.b1=g;
    }
    if(d!==null){
        c=Math.floor(d*f.a/f.b);
        if(h.a==1){
            j.a2=j.a1+c;
        }else{
            j.a1=j.a1=j.a2-c;
        }
    }
    return j;
},
drawArea:function(){
    var h=this.calcW(),e=this.calcH();
    var g="px",c=[this.areaCoords.x1+g,this.areaCoords.y1+g,h+g,e+g,this.areaCoords.x2+g,this.areaCoords.y2+g,(this.img.width-this.areaCoords.x2)+g,(this.img.height-this.areaCoords.y2)+g];
    var f=this.selArea.style;
    f.left=c[0];
    f.top=c[1];
    f.width=c[2];
    f.height=c[3];
    var i=Math.ceil((h-6)/2)+g,d=Math.ceil((e-6)/2)+g;
    this.handleN.style.left=i;
    this.handleE.style.top=d;
    this.handleS.style.left=i;
    this.handleW.style.top=d;
    this.north.style.height=c[1];
    var a=this.east.style;
    a.top=c[1];
    a.height=c[3];
    a.left=c[4];
    a.width=c[6];
    var j=this.south.style;
    j.top=c[5];
    j.height=c[7];
    var b=this.west.style;
    b.top=c[1];
    b.height=c[3];
    b.width=c[0];
    this.subDrawArea();
    this.forceReRender();
},
forceReRender:function(){
    if(this.isIE||this.isWebKit){
        var g=document.createTextNode(" ");
        var e,b,f,a;
        if(this.isIE){
            fixEl=this.selArea;
        }else{
            if(this.isWebKit){
                fixEl=document.getElementsByClassName("imgCrop_marqueeSouth",this.imgWrap)[0];
                e=new Element("div");
                e.style.visibility="hidden";
                var c=["SE","S","SW"];
                for(a=0;a<c.length;a++){
                    b=document.getElementsByClassName("imgCrop_handle"+c[a],this.selArea)[0];
                    if(b.childNodes.length){
                        b.removeChild(b.childNodes[0]);
                    }
                    b.appendChild(e);
                }
                }
            }
    fixEl.appendChild(g);
fixEl.removeChild(g);
}
},
startResize:function(a){
    this.startCoords=this.cloneCoords(this.areaCoords);
    this.resizing=true;
    this.resizeHandle=Event.element(a).classNames().toString().replace(/([^N|NE|E|SE|S|SW|W|NW])+/,"");
    Event.stop(a);
},
startDrag:function(a){
    this.selArea.show();
    this.clickCoords=this.getCurPos(a);
    this.setAreaCoords({
        x1:this.clickCoords.x,
        y1:this.clickCoords.y,
        x2:this.clickCoords.x,
        y2:this.clickCoords.y
        },false,false,null);
    this.dragging=true;
    this.onDrag(a);
    Event.stop(a);
},
getCurPos:function(b){
    var a=this.imgWrap,c=Element.cumulativeOffset(a);
    while(a.nodeName!="BODY"){
        c[1]-=a.scrollTop||0;
        c[0]-=a.scrollLeft||0;
        a=a.parentNode;
    }
    return{
        x:Event.pointerX(b)-c[0],
        y:Event.pointerY(b)-c[1]
        };

},
onDrag:function(f){
    if(this.dragging||this.resizing){
        var b=null,a=this.getCurPos(f),d=this.cloneCoords(this.areaCoords),c={
            x:1,
            y:1
        };

        if(this.dragging){
            if(a.x<this.clickCoords.x){
                c.x=-1;
            }
            if(a.y<this.clickCoords.y){
                c.y=-1;
            }
            this.transformCoords(a.x,this.clickCoords.x,d,"x");
            this.transformCoords(a.y,this.clickCoords.y,d,"y");
        }else{
            if(this.resizing){
                b=this.resizeHandle;
                if(b.match(/E/)){
                    this.transformCoords(a.x,this.startCoords.x1,d,"x");
                    if(a.x<this.startCoords.x1){
                        c.x=-1;
                    }
                }else{
                if(b.match(/W/)){
                    this.transformCoords(a.x,this.startCoords.x2,d,"x");
                    if(a.x<this.startCoords.x2){
                        c.x=-1;
                    }
                }
            }
        if(b.match(/N/)){
        this.transformCoords(a.y,this.startCoords.y2,d,"y");
        if(a.y<this.startCoords.y2){
            c.y=-1;
        }
    }else{
    if(b.match(/S/)){
        this.transformCoords(a.y,this.startCoords.y1,d,"y");
        if(a.y<this.startCoords.y1){
            c.y=-1;
        }
    }
}
}
}
this.setAreaCoords(d,false,f.shiftKey,c,b);
this.drawArea();
Event.stop(f);
}
},
transformCoords:function(b,a,e,d){
    var c=[b,a];
    if(b>a){
        c.reverse();
    }
    e[d+"1"]=c[0];
    e[d+"2"]=c[1];
},
endCrop:function(){
    this.dragging=false;
    this.resizing=false;
    this.options.onEndCrop(this.areaCoords,{
        width:this.calcW(),
        height:this.calcH()
        });
},
subInitialize:function(){},
subDrawArea:function(){}
});
Cropper.ImgWithPreview=Class.create(Cropper.Img,{
    subInitialize:function(){
        this.hasPreviewImg=false;
        if(typeof(this.options.previewWrap)!="undefined"&&this.options.minWidth>0&&this.options.minHeight>0){
            this.previewWrap=$(this.options.previewWrap);
            this.previewImg=this.img.cloneNode(false);
            this.previewImg.id="imgCrop_"+this.previewImg.id;
            this.options.displayOnInit=true;
            this.hasPreviewImg=true;
            this.previewWrap.addClassName("imgCrop_previewWrap");
            this.previewWrap.setStyle({
                width:this.options.minWidth+"px",
                height:this.options.minHeight+"px"
                });
            this.previewWrap.appendChild(this.previewImg);
        }
    },
subDrawArea:function(){
    if(this.hasPreviewImg){
        var d=this.calcW(),e=this.calcH();
        var f={
            x:this.imgW/d,
            y:this.imgH/e
            };

        var c={
            x:d/this.options.minWidth,
            y:e/this.options.minHeight
            };

        var a={
            w:Math.ceil(this.options.minWidth*f.x)+"px",
            h:Math.ceil(this.options.minHeight*f.y)+"px",
            x:"-"+Math.ceil(this.areaCoords.x1/c.x)+"px",
            y:"-"+Math.ceil(this.areaCoords.y1/c.y)+"px"
            };

        var b=this.previewImg.style;
        b.width=a.w;
        b.height=a.h;
        b.left=a.x;
        b.top=a.y;
    }
}
});
