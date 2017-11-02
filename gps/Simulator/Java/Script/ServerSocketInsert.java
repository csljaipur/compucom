import java.io.BufferedOutputStream;
import java.io.BufferedInputStream;
import java.io.IOException;
import java.io.OutputStream;
import java.io.InputStreamReader;
import java.io.InputStream;
import java.io.BufferedReader;
import java.util.StringTokenizer;
import java.net.ServerSocket;
import java.net.Socket;

public class ServerSocketInsert {
  public static void main(String[] args) throws IOException {
    int port = 7070;

    ServerSocket server = new ServerSocket(port);
    System.out.println("Curl Server Started ( application port : "+port+" ) ...");
    int clientId = 0;
    while (true) {
      Socket socket = server.accept();
      System.out.println(++clientId+" Client Connected : ");
      Thread stuffer = new StuffThread(socket,clientId);
      stuffer.start();
    }
  }
}

class StuffThread extends Thread {
 
  private Socket socket;
  private int clientId;
  MessageMediation messagemediation; 
  int status = 1;
  public StuffThread(Socket socket, int clientId){
	this.socket = socket;
	this.clientId = clientId;
	connectDB();
  }

  public void run()
  {
  	boolean isnew = true;
	try
	{
		while(true)
		{
			if (status == 0)
			{
				System.out.println("Manoj Client Id - "+clientId+ " is going to closed");	
				socket.close();
				break;
			}
			System.out.println("Manoj Client Id - "+clientId);	
			if(isnew)
			{
				
				isnew = readWriteMessage();
			}
			Thread.sleep(10000);
		}
    } catch (Exception e)
    {	e.printStackTrace(System.err); }
  }
  
  public boolean readWriteMessage()
  {

  	boolean isnew = false;
    try{
    	
    
    	if(socket != null)
      	{
      		
			InputStream in = new BufferedInputStream(socket.getInputStream());      
      		int i = 0;
      	    
      		int buffSize = in.available();
      		StringBuilder sb = new StringBuilder(buffSize);
      		
      		while (in.available() >= 0) {
      			
      			i = in.read();
      			sb.append((char) i);
      			if(in.available() == 0)
      				break;			;
					
	  		}	
	  		
	  		String message = sb.toString(); 
	  		System.out.println("\nClient Id - "+clientId+" -> "+message);	
	  		 		
   			messagemediation.parseMessage(message);   			
    		isnew = true;
      	}
      
    } catch (Exception e)
    {
    	status = 0;
    	System.out.println("Client Socket - "+clientId+" has been closed - "+e.getMessage());
    	
    }
    return isnew;  	
  }
   	
  public void connectDB()
  {
  	messagemediation = new MessageMediation();
	messagemediation.connectDB();	
  }
}